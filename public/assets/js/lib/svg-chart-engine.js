/**
 * SVG Chart Engine (100% Cross-Browser Vector Replacement for HTML5 Canvas Chart.js)
 * Prevents black <canvas> rendering issues in Mozilla Firefox / GPU-restricted browsers
 * by rendering crisp, interactive <svg> charts directly into the chart container.
 */
(function (global) {
    'use strict';

    // Inject global CSS to ensure hidden canvases never show a black box in Firefox
    const styleId = 'eh-svg-chart-engine-styles';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            canvas[data-svg-chart-hidden="true"] {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            .eh-svg-chart-host {
                position: relative;
                width: 100%;
                height: 100%;
                min-height: 240px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                user-select: none;
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
            .eh-svg-chart-svg {
                width: 100%;
                flex: 1;
                min-height: 195px;
                overflow: visible;
                display: block;
            }
            .eh-svg-chart-legend {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 8px 14px;
                padding-top: 6px;
                font-size: 11px;
                color: #475569;
                font-weight: 600;
            }
            .eh-svg-chart-legend-item {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                line-height: 1.2;
            }
            .eh-svg-chart-legend-swatch {
                width: 11px;
                height: 11px;
                border-radius: 3px;
                flex-shrink: 0;
            }
            .eh-svg-chart-tooltip {
                position: fixed;
                z-index: 99999;
                pointer-events: none;
                background: rgba(15, 23, 42, 0.94);
                color: #f8fafc;
                padding: 7px 11px;
                border-radius: 8px;
                font-size: 11.5px;
                font-weight: 600;
                line-height: 1.4;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
                border: 1px solid rgba(148, 163, 184, 0.25);
                opacity: 0;
                transition: opacity 0.1s ease;
                white-space: nowrap;
            }
            .eh-svg-bar-rect,
            .eh-svg-slice-path,
            .eh-svg-radar-point {
                transition: opacity 0.15s ease, filter 0.15s ease;
                cursor: pointer;
            }
            .eh-svg-bar-rect:hover,
            .eh-svg-slice-path:hover {
                filter: brightness(1.08);
                opacity: 0.92;
            }
            .eh-svg-radar-point:hover {
                r: 6px;
                filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.6));
            }
        `;
        document.head.appendChild(style);
    }

    let tooltipEl = null;
    function getTooltip() {
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.className = 'eh-svg-chart-tooltip';
            document.body.appendChild(tooltipEl);
        }
        return tooltipEl;
    }

    function showTooltip(evt, title, labelText, color) {
        const tip = getTooltip();
        const swatch = color
            ? `<span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:${escapeHtml(color)};margin-right:6px;"></span>`
            : '';
        tip.innerHTML =
            (title ? `<div style="font-size:10.5px;color:#94a3b8;margin-bottom:2px;">${escapeHtml(title)}</div>` : '') +
            `<div>${swatch}${escapeHtml(labelText)}</div>`;
        tip.style.opacity = '1';
        moveTooltip(evt);
    }

    function moveTooltip(evt) {
        if (!tooltipEl) return;
        const pad = 14;
        let x = evt.clientX + pad;
        let y = evt.clientY - 12;
        const rect = tooltipEl.getBoundingClientRect();
        if (x + rect.width > window.innerWidth - 10) {
            x = evt.clientX - rect.width - pad;
        }
        if (y + rect.height > window.innerHeight - 10) {
            y = window.innerHeight - rect.height - 10;
        }
        if (y < 8) y = 8;
        tooltipEl.style.left = x + 'px';
        tooltipEl.style.top = y + 'px';
    }

    function hideTooltip() {
        if (tooltipEl) {
            tooltipEl.style.opacity = '0';
        }
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function resolveColor(colorProp, index, fallback) {
        if (Array.isArray(colorProp)) {
            return colorProp[index % colorProp.length] || fallback;
        }
        return colorProp || fallback;
    }

    function formatTickValue(val, scaleOpts) {
        if (scaleOpts && scaleOpts.ticks && typeof scaleOpts.ticks.callback === 'function') {
            try {
                const res = scaleOpts.ticks.callback(val);
                if (res !== undefined && res !== null) return String(res);
            } catch (e) {}
        }
        if (typeof val === 'number') {
            return Number.isInteger(val) ? String(val) : val.toFixed(1).replace(/\.0$/, '');
        }
        return String(val);
    }

    function formatTooltipLabel(dataset, rawVal, label, options) {
        const cb = options?.plugins?.tooltip?.callbacks?.label;
        if (typeof cb === 'function') {
            try {
                const res = cb({
                    dataset: dataset,
                    raw: rawVal,
                    label: label,
                    formattedValue: String(rawVal)
                });
                if (res) return String(res);
            } catch (e) {}
        }
        const dsLabel = dataset.label ? `${dataset.label}: ` : '';
        return `${dsLabel}${rawVal}`;
    }

    function niceMax(maxVal, stepHint) {
        if (maxVal <= 0) return stepHint || 10;
        if (stepHint && stepHint > 0) {
            return Math.ceil(maxVal / stepHint) * stepHint;
        }
        const magnitude = Math.pow(10, Math.floor(Math.log10(maxVal)));
        const normalized = maxVal / magnitude;
        let niceNorm;
        if (normalized <= 1.2) niceNorm = 1.2;
        else if (normalized <= 1.5) niceNorm = 1.5;
        else if (normalized <= 2) niceNorm = 2;
        else if (normalized <= 2.5) niceNorm = 2.5;
        else if (normalized <= 3) niceNorm = 3;
        else if (normalized <= 4) niceNorm = 4;
        else if (normalized <= 5) niceNorm = 5;
        else if (normalized <= 6) niceNorm = 6;
        else if (normalized <= 8) niceNorm = 8;
        else niceNorm = 10;
        return Math.ceil(niceNorm * magnitude);
    }

    class SvgChart {
        constructor(target, config) {
            this.canvas = typeof target === 'string' ? document.getElementById(target) : target;
            if (this.canvas && this.canvas.canvas) {
                this.canvas = this.canvas.canvas;
            }
            this.config = config || {};
            this.host = null;
            this._resizeHandler = null;
            this.render();
        }

        destroy() {
            hideTooltip();
            if (this._resizeHandler) {
                window.removeEventListener('resize', this._resizeHandler);
                this._resizeHandler = null;
            }
            if (this.host && this.host.parentNode) {
                this.host.parentNode.removeChild(this.host);
            }
            this.host = null;
        }

        update() {
            this.render();
        }

        render() {
            if (!this.canvas || !this.canvas.parentNode) return;

            // Hide original <canvas> so Firefox never renders a black rectangle
            this.canvas.setAttribute('data-svg-chart-hidden', 'true');

            const parent = this.canvas.parentNode;
            if (this.host && this.host.parentNode === parent) {
                this.host.innerHTML = '';
            } else {
                const existing = parent.querySelector('.eh-svg-chart-host');
                if (existing) existing.remove();
                this.host = document.createElement('div');
                this.host.className = 'eh-svg-chart-host';
                parent.appendChild(this.host);
            }

            const type = (this.config.type || 'bar').toLowerCase();
            if (type === 'radar') {
                this.renderRadar();
            } else if (type === 'doughnut' || type === 'pie') {
                this.renderDoughnut();
            } else {
                this.renderBar();
            }
        }

        /* ==================== 1. RADAR CHART ==================== */
        renderRadar() {
            const data = this.config.data || {};
            const options = this.config.options || {};
            const labels = data.labels || [];
            const datasets = data.datasets || [];
            const count = labels.length;
            if (count < 3) return;

            const rScale = options.scales?.r || {};
            let maxVal = rScale.max || 100;
            datasets.forEach(ds => {
                (ds.data || []).forEach(v => {
                    if (Number(v) > maxVal) maxVal = Number(v);
                });
            });
            const stepSize = rScale.ticks?.stepSize || Math.round(maxVal / 3) || 50;
            const steps = [];
            for (let s = stepSize; s <= maxVal; s += stepSize) {
                steps.push(s);
            }
            if (steps[steps.length - 1] !== maxVal) {
                steps.push(maxVal);
            }

            const vw = 460;
            const vh = 270;
            const cx = vw / 2;
            const cy = vh / 2 + 6;
            const radius = Math.min(vw, vh) * 0.36;

            const angleFor = i => (Math.PI * 2 * i) / count - Math.PI / 2;
            const pointFor = (i, val) => {
                const ratio = Math.max(0, Math.min(val, maxVal)) / maxVal;
                const a = angleFor(i);
                return [cx + Math.cos(a) * radius * ratio, cy + Math.sin(a) * radius * ratio];
            };

            let svgParts = [];
            svgParts.push(`<svg class="eh-svg-chart-svg" viewBox="0 0 ${vw} ${vh}" preserveAspectRatio="xMidYMid meet">`);

            // Concentric polygon webs
            steps.forEach(stepVal => {
                const pts = labels.map((_, i) => pointFor(i, stepVal).map(n => n.toFixed(1)).join(',')).join(' ');
                svgParts.push(
                    `<polygon points="${pts}" fill="none" stroke="#e2e8f0" stroke-width="1" />`
                );
                const labelY = (cy - (stepVal / maxVal) * radius).toFixed(1);
                const tickText = formatTickValue(stepVal, rScale);
                svgParts.push(
                    `<text x="${cx + 4}" y="${labelY - 2}" fill="#94a3b8" font-size="9.5" font-weight="600">${escapeHtml(tickText)}</text>`
                );
            });

            // Axis spokes & outer vertex labels
            labels.forEach((lbl, i) => {
                const [ox, oy] = pointFor(i, maxVal);
                svgParts.push(
                    `<line x1="${cx}" y1="${cy}" x2="${ox.toFixed(1)}" y2="${oy.toFixed(1)}" stroke="#cbd5e1" stroke-width="1" stroke-dasharray="2 2" />`
                );

                const a = angleFor(i);
                const labelDist = radius + 22;
                const lx = cx + Math.cos(a) * labelDist;
                const ly = cy + Math.sin(a) * labelDist;
                let anchor = 'middle';
                if (Math.cos(a) > 0.25) anchor = 'start';
                else if (Math.cos(a) < -0.25) anchor = 'end';

                svgParts.push(
                    `<text x="${lx.toFixed(1)}" y="${(ly + 3).toFixed(1)}" text-anchor="${anchor}" fill="#334155" font-size="10.5" font-weight="700">${escapeHtml(lbl)}</text>`
                );
            });

            // Datasets polygons & interactive points
            datasets.forEach((ds, dsIdx) => {
                const vals = ds.data || [];
                const stroke = ds.borderColor || '#2563eb';
                const fill = ds.backgroundColor || 'rgba(37, 99, 235, 0.18)';
                const strokeW = ds.borderWidth || 2;
                const dash = Array.isArray(ds.borderDash) ? `stroke-dasharray="${ds.borderDash.join(' ')}"` : '';

                const pts = labels.map((_, i) => pointFor(i, Number(vals[i] || 0)).map(n => n.toFixed(1)).join(',')).join(' ');
                svgParts.push(
                    `<polygon points="${pts}" fill="${escapeHtml(fill)}" stroke="${escapeHtml(stroke)}" stroke-width="${strokeW}" ${dash} />`
                );

                const ptRadius = ds.pointRadius !== undefined ? ds.pointRadius : 3.8;
                if (ptRadius > 0) {
                    labels.forEach((lbl, i) => {
                        const rawVal = Number(vals[i] || 0);
                        const [px, py] = pointFor(i, rawVal);
                        const tipText = formatTooltipLabel(ds, rawVal, lbl, options);
                        svgParts.push(
                            `<circle class="eh-svg-radar-point" cx="${px.toFixed(1)}" cy="${py.toFixed(1)}" r="${ptRadius}" fill="${escapeHtml(ds.pointBackgroundColor || stroke)}" stroke="#ffffff" stroke-width="1.5" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(stroke)}" />`
                        );
                    });
                }
            });

            svgParts.push('</svg>');
            this.host.innerHTML = svgParts.join('');

            this.bindTooltipEvents();
            this.renderLegend(datasets.map(ds => ({
                label: ds.label || '',
                color: ds.borderColor || '#2563eb'
            })), options);
        }

        /* ==================== 2. DOUGHNUT / PIE CHART ==================== */
        renderDoughnut() {
            const data = this.config.data || {};
            const options = this.config.options || {};
            const labels = data.labels || [];
            const ds = (data.datasets && data.datasets[0]) || { data: [] };
            const values = (ds.data || []).map(v => Math.max(0, Number(v || 0)));
            const colors = ds.backgroundColor || ['#10b981', '#2563eb', '#f59e0b', '#64748b', '#f43f5e', '#9333ea'];
            const total = values.reduce((a, b) => a + b, 0);

            const vw = 360;
            const vh = 220;
            const cx = vw / 2;
            const cy = vh / 2;
            const outerR = 86;
            const innerR = 54;

            let svgParts = [];
            svgParts.push(`<svg class="eh-svg-chart-svg" viewBox="0 0 ${vw} ${vh}" preserveAspectRatio="xMidYMid meet">`);

            if (total <= 0) {
                svgParts.push(`<circle cx="${cx}" cy="${cy}" r="${outerR}" fill="none" stroke="#e2e8f0" stroke-width="${outerR - innerR}" />`);
                svgParts.push(`<text x="${cx}" y="${cy + 4}" text-anchor="middle" fill="#94a3b8" font-size="12" font-weight="600">0 Data</text>`);
            } else {
                let startAngle = -Math.PI / 2;
                values.forEach((val, idx) => {
                    if (val <= 0) return;
                    const sliceAngle = (val / total) * (Math.PI * 2);
                    const endAngle = startAngle + sliceAngle;
                    const color = resolveColor(colors, idx, '#2563eb');
                    const lbl = labels[idx] || `Item ${idx + 1}`;
                    const pct = ((val / total) * 100).toFixed(1);
                    const tipText = `${lbl}: ${val.toLocaleString('id-ID')} (${pct}%)`;

                    if (sliceAngle >= Math.PI * 2 - 0.001) {
                        const midR = (outerR + innerR) / 2;
                        const ringW = outerR - innerR;
                        svgParts.push(
                            `<circle class="eh-svg-slice-path" cx="${cx}" cy="${cy}" r="${midR}" fill="none" stroke="${escapeHtml(color)}" stroke-width="${ringW}" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                        );
                    } else {
                        const x1 = cx + outerR * Math.cos(startAngle);
                        const y1 = cy + outerR * Math.sin(startAngle);
                        const x2 = cx + outerR * Math.cos(endAngle);
                        const y2 = cy + outerR * Math.sin(endAngle);
                        const x3 = cx + innerR * Math.cos(endAngle);
                        const y3 = cy + innerR * Math.sin(endAngle);
                        const x4 = cx + innerR * Math.cos(startAngle);
                        const y4 = cy + innerR * Math.sin(startAngle);
                        const largeArc = sliceAngle > Math.PI ? 1 : 0;

                        const d = [
                            `M ${x1.toFixed(2)} ${y1.toFixed(2)}`,
                            `A ${outerR} ${outerR} 0 ${largeArc} 1 ${x2.toFixed(2)} ${y2.toFixed(2)}`,
                            `L ${x3.toFixed(2)} ${y3.toFixed(2)}`,
                            `A ${innerR} ${innerR} 0 ${largeArc} 0 ${x4.toFixed(2)} ${y4.toFixed(2)}`,
                            'Z'
                        ].join(' ');

                        svgParts.push(
                            `<path class="eh-svg-slice-path" d="${d}" fill="${escapeHtml(color)}" stroke="#ffffff" stroke-width="2" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                        );
                    }

                    startAngle = endAngle;
                });

                svgParts.push(
                    `<text x="${cx}" y="${cy - 4}" text-anchor="middle" fill="#64748b" font-size="10" font-weight="700">TOTAL</text>` +
                    `<text x="${cx}" y="${cy + 14}" text-anchor="middle" fill="#0f172a" font-size="15" font-weight="800">${escapeHtml(total.toLocaleString('id-ID'))}</text>`
                );
            }

            svgParts.push('</svg>');
            this.host.innerHTML = svgParts.join('');

            this.bindTooltipEvents();
            this.renderLegend(labels.map((lbl, idx) => ({
                label: lbl,
                color: resolveColor(colors, idx, '#2563eb')
            })), options);
        }

        /* ==================== 3. BAR CHART (VERTICAL & HORIZONTAL, GROUPED & STACKED) ==================== */
        renderBar() {
            const data = this.config.data || {};
            const options = this.config.options || {};
            const labels = data.labels || [];
            const datasets = data.datasets || [];
            const isHorizontal = options.indexAxis === 'y';
            const isStacked = Boolean(options.scales?.x?.stacked || options.scales?.y?.stacked);

            if (isHorizontal) {
                this.renderHorizontalBar(labels, datasets, options, isStacked);
            } else {
                this.renderVerticalBar(labels, datasets, options, isStacked);
            }
        }

        renderVerticalBar(labels, datasets, options, isStacked) {
            const count = Math.max(labels.length, 1);
            const dsCount = Math.max(datasets.length, 1);
            const yScale = options.scales?.y || {};

            let rawMax = 0;
            if (isStacked) {
                labels.forEach((_, i) => {
                    const sum = datasets.reduce((acc, ds) => acc + Math.max(0, Number(ds.data?.[i] || 0)), 0);
                    if (sum > rawMax) rawMax = sum;
                });
            } else {
                datasets.forEach(ds => {
                    (ds.data || []).forEach(v => {
                        if (Number(v) > rawMax) rawMax = Number(v);
                    });
                });
            }
            const maxVal = yScale.max ? Math.max(yScale.max, rawMax) : niceMax(rawMax);

            const vw = 560;
            const vh = 255;
            const padLeft = 46;
            const padRight = 16;
            const padTop = 18;
            const padBottom = 42;
            const plotW = vw - padLeft - padRight;
            const plotH = vh - padTop - padBottom;

            let svgParts = [];
            svgParts.push(`<svg class="eh-svg-chart-svg" viewBox="0 0 ${vw} ${vh}" preserveAspectRatio="xMidYMid meet">`);

            // Y-axis grid lines & labels (5 steps)
            const gridSteps = 4;
            for (let g = 0; g <= gridSteps; g++) {
                const ratio = g / gridSteps;
                const val = maxVal * ratio;
                const y = padTop + plotH - ratio * plotH;
                const tickText = formatTickValue(Math.round(val * 10) / 10, yScale);

                svgParts.push(
                    `<line x1="${padLeft}" y1="${y.toFixed(1)}" x2="${vw - padRight}" y2="${y.toFixed(1)}" stroke="${g === 0 ? '#cbd5e1' : '#f1f5f9'}" stroke-width="1" />` +
                    `<text x="${padLeft - 8}" y="${(y + 3.5).toFixed(1)}" text-anchor="end" fill="#64748b" font-size="10" font-weight="600">${escapeHtml(tickText)}</text>`
                );
            }

            const slotW = plotW / count;
            labels.forEach((lbl, i) => {
                const slotX = padLeft + i * slotW;
                const centerX = slotX + slotW / 2;

                if (isStacked) {
                    const barW = Math.min(44, slotW * 0.56);
                    const barX = centerX - barW / 2;
                    let currentBottomY = padTop + plotH;

                    datasets.forEach((ds, dsIdx) => {
                        const val = Math.max(0, Number(ds.data?.[i] || 0));
                        if (val <= 0) return;
                        const barH = Math.max(2, (val / maxVal) * plotH);
                        const barY = currentBottomY - barH;
                        const color = resolveColor(ds.backgroundColor, i, '#2563eb');
                        const tipText = formatTooltipLabel(ds, val, lbl, options);

                        svgParts.push(
                            `<rect class="eh-svg-bar-rect" x="${barX.toFixed(1)}" y="${barY.toFixed(1)}" width="${barW.toFixed(1)}" height="${barH.toFixed(1)}" rx="3" fill="${escapeHtml(color)}" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                        );
                        currentBottomY = barY;
                    });
                } else {
                    const groupW = Math.min(slotW * 0.74, dsCount * 38);
                    const startX = centerX - groupW / 2;
                    const subGap = dsCount > 1 ? 4 : 0;
                    const barW = Math.max(6, (groupW - subGap * (dsCount - 1)) / dsCount);

                    datasets.forEach((ds, dsIdx) => {
                        const val = Math.max(0, Number(ds.data?.[i] || 0));
                        const barH = val > 0 ? Math.max(3, (val / maxVal) * plotH) : 0;
                        const barX = startX + dsIdx * (barW + subGap);
                        const barY = padTop + plotH - barH;
                        const color = resolveColor(ds.backgroundColor, i, '#2563eb');
                        const tipText = formatTooltipLabel(ds, val, lbl, options);

                        if (barH > 0) {
                            svgParts.push(
                                `<rect class="eh-svg-bar-rect" x="${barX.toFixed(1)}" y="${barY.toFixed(1)}" width="${barW.toFixed(1)}" height="${barH.toFixed(1)}" rx="4" fill="${escapeHtml(color)}" ` +
                                `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                            );
                        }
                    });
                }

                // X-axis label
                const shortLbl = String(lbl).length > 18 ? String(lbl).slice(0, 16) + '…' : String(lbl);
                svgParts.push(
                    `<text x="${centerX.toFixed(1)}" y="${vh - 16}" text-anchor="middle" fill="#475569" font-size="10" font-weight="600">${escapeHtml(shortLbl)}</text>`
                );
            });

            svgParts.push('</svg>');
            this.host.innerHTML = svgParts.join('');

            this.bindTooltipEvents();
            if (options.plugins?.legend?.display !== false) {
                this.renderLegend(datasets.map(ds => ({
                    label: ds.label || '',
                    color: Array.isArray(ds.backgroundColor) ? ds.backgroundColor[0] : (ds.backgroundColor || '#2563eb')
                })), options);
            }
        }

        renderHorizontalBar(labels, datasets, options, isStacked) {
            const count = Math.max(labels.length, 1);
            const dsCount = Math.max(datasets.length, 1);

            let rawMax = 0;
            if (isStacked) {
                labels.forEach((_, i) => {
                    const sum = datasets.reduce((acc, ds) => acc + Math.max(0, Number(ds.data?.[i] || 0)), 0);
                    if (sum > rawMax) rawMax = sum;
                });
            } else {
                datasets.forEach(ds => {
                    (ds.data || []).forEach(v => {
                        if (Number(v) > rawMax) rawMax = Number(v);
                    });
                });
            }
            const maxVal = niceMax(rawMax);

            const vw = 560;
            const rowH = Math.max(22, Math.min(32, Math.floor(220 / count)));
            const padTop = 12;
            const padBottom = 28;
            const padLeft = 110;
            const padRight = 20;
            const vh = padTop + count * rowH + padBottom;
            const plotW = vw - padLeft - padRight;
            const plotH = count * rowH;

            let svgParts = [];
            svgParts.push(`<svg class="eh-svg-chart-svg" viewBox="0 0 ${vw} ${vh}" preserveAspectRatio="xMidYMid meet">`);

            const gridSteps = 4;
            for (let g = 0; g <= gridSteps; g++) {
                const ratio = g / gridSteps;
                const val = Math.round(maxVal * ratio);
                const x = padLeft + ratio * plotW;
                svgParts.push(
                    `<line x1="${x.toFixed(1)}" y1="${padTop}" x2="${x.toFixed(1)}" y2="${padTop + plotH}" stroke="${g === 0 ? '#cbd5e1' : '#f1f5f9'}" stroke-width="1" />` +
                    `<text x="${x.toFixed(1)}" y="${vh - 8}" text-anchor="middle" fill="#64748b" font-size="9.5" font-weight="600">${val}</text>`
                );
            }

            labels.forEach((lbl, i) => {
                const slotY = padTop + i * rowH;
                const centerY = slotY + rowH / 2;
                const shortLbl = String(lbl).length > 15 ? String(lbl).slice(0, 14) + '…' : String(lbl);

                svgParts.push(
                    `<text x="${padLeft - 8}" y="${(centerY + 3.5).toFixed(1)}" text-anchor="end" fill="#334155" font-size="10" font-weight="600">${escapeHtml(shortLbl)}</text>`
                );

                if (isStacked) {
                    const barH = Math.min(16, rowH * 0.64);
                    const barY = centerY - barH / 2;
                    let currentX = padLeft;

                    datasets.forEach(ds => {
                        const val = Math.max(0, Number(ds.data?.[i] || 0));
                        if (val <= 0) return;
                        const barW = Math.max(2, (val / maxVal) * plotW);
                        const color = resolveColor(ds.backgroundColor, i, '#2563eb');
                        const tipText = formatTooltipLabel(ds, val, lbl, options);

                        svgParts.push(
                            `<rect class="eh-svg-bar-rect" x="${currentX.toFixed(1)}" y="${barY.toFixed(1)}" width="${barW.toFixed(1)}" height="${barH.toFixed(1)}" rx="3" fill="${escapeHtml(color)}" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                        );
                        currentX += barW;
                    });
                } else {
                    const groupH = rowH * 0.72;
                    const barH = Math.max(4, groupH / dsCount);
                    const startY = centerY - groupH / 2;

                    datasets.forEach((ds, dsIdx) => {
                        const val = Math.max(0, Number(ds.data?.[i] || 0));
                        if (val <= 0) return;
                        const barW = Math.max(2, (val / maxVal) * plotW);
                        const barY = startY + dsIdx * barH;
                        const color = resolveColor(ds.backgroundColor, i, '#2563eb');
                        const tipText = formatTooltipLabel(ds, val, lbl, options);

                        svgParts.push(
                            `<rect class="eh-svg-bar-rect" x="${padLeft}" y="${barY.toFixed(1)}" width="${barW.toFixed(1)}" height="${(barH - 1).toFixed(1)}" rx="3" fill="${escapeHtml(color)}" ` +
                            `data-tip-title="${escapeHtml(lbl)}" data-tip-label="${escapeHtml(tipText)}" data-tip-color="${escapeHtml(color)}" />`
                        );
                    });
                }
            });

            svgParts.push('</svg>');
            this.host.innerHTML = svgParts.join('');

            this.bindTooltipEvents();
            if (options.plugins?.legend?.display !== false) {
                this.renderLegend(datasets.map(ds => ({
                    label: ds.label || '',
                    color: Array.isArray(ds.backgroundColor) ? ds.backgroundColor[0] : (ds.backgroundColor || '#2563eb')
                })), options);
            }
        }

        renderLegend(items, options) {
            const valid = (items || []).filter(it => it.label);
            if (valid.length === 0 || options?.plugins?.legend?.display === false) return;

            const legendDiv = document.createElement('div');
            legendDiv.className = 'eh-svg-chart-legend';
            legendDiv.innerHTML = valid.map(it => `
                <span class="eh-svg-chart-legend-item">
                    <span class="eh-svg-chart-legend-swatch" style="background:${escapeHtml(it.color)};"></span>
                    <span>${escapeHtml(it.label)}</span>
                </span>
            `).join('');
            this.host.appendChild(legendDiv);
        }

        bindTooltipEvents() {
            if (!this.host) return;
            const interactiveEls = this.host.querySelectorAll('[data-tip-label]');
            interactiveEls.forEach(el => {
                el.addEventListener('mouseenter', e => {
                    showTooltip(
                        e,
                        el.getAttribute('data-tip-title') || '',
                        el.getAttribute('data-tip-label') || '',
                        el.getAttribute('data-tip-color') || ''
                    );
                });
                el.addEventListener('mousemove', e => moveTooltip(e));
                el.addEventListener('mouseleave', () => hideTooltip());
            });
        }
    }

    // Override global Chart with SvgChart so all charts use 100% vector SVG in Firefox, Chrome, Edge & Safari
    global.Chart = SvgChart;
})(window);

