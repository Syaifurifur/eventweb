/**
 * Pure JavaScript ISO/IEC 18004 QR Code SVG & Poster PNG Generator
 * Renders scannable QR codes in <svg> and exports high-res PNG posters.
 */
(function (global) {
    'use strict';

    // GF(256) exponent & log tables for Reed-Solomon
    const EXP_TABLE = new Array(256);
    const LOG_TABLE = new Array(256);
    for (let i = 0; i < 8; i++) EXP_TABLE[i] = 1 << i;
    for (let i = 8; i < 256; i++) {
        EXP_TABLE[i] = EXP_TABLE[i - 4] ^ EXP_TABLE[i - 5] ^ EXP_TABLE[i - 6] ^ EXP_TABLE[i - 8];
    }
    for (let i = 0; i < 255; i++) LOG_TABLE[EXP_TABLE[i]] = i;

    function glog(n) {
        if (n < 1) throw new Error('glog(' + n + ')');
        return LOG_TABLE[n];
    }
    function gexp(n) {
        while (n < 0) n += 255;
        while (n >= 256) n -= 255;
        return EXP_TABLE[n];
    }

    function Polynomial(num, shift) {
        let offset = 0;
        while (offset < num.length && num[offset] === 0) offset++;
        this.num = new Array(num.length - offset + shift);
        for (let i = 0; i < num.length - offset; i++) {
            this.num[i] = num[i + offset];
        }
        for (let i = num.length - offset; i < this.num.length; i++) {
            this.num[i] = 0;
        }
    }
    Polynomial.prototype = {
        get: function (index) { return this.num[index]; },
        getLength: function () { return this.num.length; },
        multiply: function (e) {
            const num = new Array(this.getLength() + e.getLength() - 1).fill(0);
            for (let i = 0; i < this.getLength(); i++) {
                for (let j = 0; j < e.getLength(); j++) {
                    num[i + j] ^= gexp(glog(this.get(i)) + glog(e.get(j)));
                }
            }
            return new Polynomial(num, 0);
        },
        mod: function (e) {
            if (this.getLength() - e.getLength() < 0) return this;
            const ratio = glog(this.get(0)) - glog(e.get(0));
            const num = this.num.slice();
            for (let i = 0; i < e.getLength(); i++) {
                num[i] ^= gexp(glog(e.get(i)) + ratio);
            }
            return new Polynomial(num, 0).mod(e);
        }
    };

    function getErrorCorrectPolynomial(errorCorrectLength) {
        let a = new Polynomial([1], 0);
        for (let i = 0; i < errorCorrectLength; i++) {
            a = a.multiply(new Polynomial([1, gexp(i)], 0));
        }
        return a;
    }

    // EC Level L block table for versions 1..10: [numBlocks1, totalCodewords1, dataCodewords1, numBlocks2, totalCodewords2, dataCodewords2]
    const RS_BLOCK_TABLE_L = [
        null,
        [1, 26, 19],
        [1, 44, 34],
        [1, 70, 55],
        [1, 100, 80],
        [1, 134, 108],
        [2, 86, 68],
        [2, 98, 78],
        [2, 121, 97],
        [2, 146, 116],
        [2, 86, 68, 2, 87, 69]
    ];

    const PATTERN_POSITION_TABLE = [
        [],
        [],
        [6, 18],
        [6, 22],
        [6, 26],
        [6, 30],
        [6, 34],
        [6, 22, 38],
        [6, 24, 42],
        [6, 26, 46],
        [6, 28, 50]
    ];

    function getRSBlocks(typeNumber) {
        const rsBlock = RS_BLOCK_TABLE_L[typeNumber];
        const list = [];
        for (let i = 0; i < rsBlock.length; i += 3) {
            const count = rsBlock[i];
            const totalCount = rsBlock[i + 1];
            const dataCount = rsBlock[i + 2];
            for (let j = 0; j < count; j++) {
                list.push({ totalCount: totalCount, dataCount: dataCount });
            }
        }
        return list;
    }

    function BitBuffer() {
        this.buffer = [];
        this.length = 0;
    }
    BitBuffer.prototype = {
        put: function (num, length) {
            for (let i = 0; i < length; i++) {
                this.putBit(((num >>> (length - i - 1)) & 1) === 1);
            }
        },
        putBit: function (bit) {
            const bufIndex = Math.floor(this.length / 8);
            if (this.buffer.length <= bufIndex) this.buffer.push(0);
            if (bit) this.buffer[bufIndex] |= (0x80 >>> (this.length % 8));
            this.length++;
        }
    };

    function getBCHDigit(data) {
        let digit = 0;
        while (data !== 0) {
            digit++;
            data >>>= 1;
        }
        return digit;
    }

    function getBCHTypeInfo(data) {
        let d = data << 10;
        const G15 = (1 << 10) | (1 << 8) | (1 << 5) | (1 << 4) | (1 << 2) | (1 << 1) | (1 << 0);
        while (getBCHDigit(d) - getBCHDigit(G15) >= 0) {
            d ^= (G15 << (getBCHDigit(d) - getBCHDigit(G15)));
        }
        return ((data << 10) | d) ^ ((1 << 14) | (1 << 12) | (1 << 10) | (1 << 4) | (1 << 1));
    }

    function getBCHTypeNumber(data) {
        let d = data << 12;
        const G18 = (1 << 12) | (1 << 11) | (1 << 10) | (1 << 9) | (1 << 8) | (1 << 5) | (1 << 2) | (1 << 0);
        while (getBCHDigit(d) - getBCHDigit(G18) >= 0) {
            d ^= (G18 << (getBCHDigit(d) - getBCHDigit(G18)));
        }
        return (data << 12) | d;
    }

    function utf8Bytes(str) {
        const out = [];
        for (let i = 0; i < str.length; i++) {
            let c = str.charCodeAt(i);
            if (c < 0x80) {
                out.push(c);
            } else if (c < 0x800) {
                out.push(0xc0 | (c >> 6), 0x80 | (c & 0x3f));
            } else {
                out.push(0xe0 | (c >> 12), 0x80 | ((c >> 6) & 0x3f), 0x80 | (c & 0x3f));
            }
        }
        return out;
    }

    function chooseTypeNumber(byteLen) {
        for (let t = 1; t <= 10; t++) {
            const blocks = getRSBlocks(t);
            let totalDataCount = 0;
            for (let i = 0; i < blocks.length; i++) totalDataCount += blocks[i].dataCount;
            const lenBits = t < 10 ? 8 : 16;
            if (byteLen * 8 + 4 + lenBits <= totalDataCount * 8) {
                return t;
            }
        }
        return 10;
    }

    function createData(typeNumber, bytes) {
        const rsBlocks = getRSBlocks(typeNumber);
        const buffer = new BitBuffer();
        // Byte mode = 4 (0100)
        buffer.put(4, 4);
        buffer.put(bytes.length, typeNumber < 10 ? 8 : 16);
        for (let i = 0; i < bytes.length; i++) {
            buffer.put(bytes[i], 8);
        }
        let totalDataCount = 0;
        for (let i = 0; i < rsBlocks.length; i++) {
            totalDataCount += rsBlocks[i].dataCount;
        }
        if (buffer.length + 4 <= totalDataCount * 8) {
            buffer.put(0, 4);
        }
        while (buffer.length % 8 !== 0) {
            buffer.putBit(false);
        }
        while (true) {
            if (buffer.length >= totalDataCount * 8) break;
            buffer.put(0xEC, 8);
            if (buffer.length >= totalDataCount * 8) break;
            buffer.put(0x11, 8);
        }

        let offset = 0;
        let maxDcCount = 0;
        let maxEcCount = 0;
        const dcdata = new Array(rsBlocks.length);
        const ecdata = new Array(rsBlocks.length);

        for (let r = 0; r < rsBlocks.length; r++) {
            const dcCount = rsBlocks[r].dataCount;
            const ecCount = rsBlocks[r].totalCount - dcCount;
            maxDcCount = Math.max(maxDcCount, dcCount);
            maxEcCount = Math.max(maxEcCount, ecCount);
            dcdata[r] = new Array(dcCount);
            for (let i = 0; i < dcdata[r].length; i++) {
                dcdata[r][i] = 0xff & buffer.buffer[i + offset];
            }
            offset += dcCount;
            const rsPoly = getErrorCorrectPolynomial(ecCount);
            const rawPoly = new Polynomial(dcdata[r], rsPoly.getLength() - 1);
            const modPoly = rawPoly.mod(rsPoly);
            ecdata[r] = new Array(rsPoly.getLength() - 1);
            for (let i = 0; i < ecdata[r].length; i++) {
                const modIndex = i + modPoly.getLength() - ecdata[r].length;
                ecdata[r][i] = (modIndex >= 0) ? modPoly.get(modIndex) : 0;
            }
        }

        const data = [];
        for (let i = 0; i < maxDcCount; i++) {
            for (let r = 0; r < rsBlocks.length; r++) {
                if (i < dcdata[r].length) data.push(dcdata[r][i]);
            }
        }
        for (let i = 0; i < maxEcCount; i++) {
            for (let r = 0; r < rsBlocks.length; r++) {
                if (i < ecdata[r].length) data.push(ecdata[r][i]);
            }
        }
        return data;
    }

    function buildMatrix(text) {
        const bytes = utf8Bytes(String(text || ''));
        const typeNumber = chooseTypeNumber(bytes.length);
        const moduleCount = typeNumber * 4 + 17;
        const modules = new Array(moduleCount);
        for (let row = 0; row < moduleCount; row++) {
            modules[row] = new Array(moduleCount).fill(null);
        }

        function setupPositionProbePattern(row, col) {
            for (let r = -1; r <= 7; r++) {
                if (row + r <= -1 || moduleCount <= row + r) continue;
                for (let c = -1; c <= 7; c++) {
                    if (col + c <= -1 || moduleCount <= col + c) continue;
                    if ((0 <= r && r <= 6 && (c === 0 || c === 6)) ||
                        (0 <= c && c <= 6 && (r === 0 || r === 6)) ||
                        (2 <= r && r <= 4 && 2 <= c && c <= 4)) {
                        modules[row + r][col + c] = true;
                    } else {
                        modules[row + r][col + c] = false;
                    }
                }
            }
        }

        setupPositionProbePattern(0, 0);
        setupPositionProbePattern(moduleCount - 7, 0);
        setupPositionProbePattern(0, moduleCount - 7);

        // Timing pattern
        for (let r = 8; r < moduleCount - 8; r++) {
            if (modules[r][6] === null) modules[r][6] = (r % 2 === 0);
        }
        for (let c = 8; c < moduleCount - 8; c++) {
            if (modules[6][c] === null) modules[6][c] = (c % 2 === 0);
        }

        // Alignment pattern
        const pos = PATTERN_POSITION_TABLE[typeNumber] || [];
        for (let i = 0; i < pos.length; i++) {
            for (let j = 0; j < pos.length; j++) {
                const row = pos[i];
                const col = pos[j];
                if (modules[row][col] !== null) continue;
                for (let r = -2; r <= 2; r++) {
                    for (let c = -2; c <= 2; c++) {
                        if (r === -2 || r === 2 || c === -2 || c === 2 || (r === 0 && c === 0)) {
                            modules[row + r][col + c] = true;
                        } else {
                            modules[row + r][col + c] = false;
                        }
                    }
                }
            }
        }

        // Type number (if >= 7)
        if (typeNumber >= 7) {
            const bits = getBCHTypeNumber(typeNumber);
            for (let i = 0; i < 18; i++) {
                const mod = ((bits >> i) & 1) === 1;
                modules[Math.floor(i / 3)][i % 3 + moduleCount - 8 - 3] = mod;
                modules[i % 3 + moduleCount - 8 - 3][Math.floor(i / 3)] = mod;
            }
        }

        // Type Info (EC Level L = 1, Mask 0)
        const maskPattern = 0;
        const typeInfoData = (1 << 3) | maskPattern;
        const bits = getBCHTypeInfo(typeInfoData);
        for (let i = 0; i < 15; i++) {
            const mod = ((bits >> i) & 1) === 1;
            if (i < 6) modules[i][8] = mod;
            else if (i < 8) modules[i + 1][8] = mod;
            else modules[moduleCount - 15 + i][8] = mod;

            if (i < 8) modules[8][moduleCount - i - 1] = mod;
            else if (i < 9) modules[8][15 - i - 1 + 1] = mod;
            else modules[8][15 - i - 1] = mod;
        }
        modules[moduleCount - 8][8] = true;

        // Map Data
        const data = createData(typeNumber, bytes);
        let inc = -1;
        let row = moduleCount - 1;
        let bitIndex = 7;
        let byteIndex = 0;

        for (let col = moduleCount - 1; col > 0; col -= 2) {
            if (col === 6) col--;
            while (true) {
                for (let c = 0; c < 2; c++) {
                    if (modules[row][col - c] === null) {
                        let dark = false;
                        if (byteIndex < data.length) {
                            dark = (((data[byteIndex] >>> bitIndex) & 1) === 1);
                        }
                        const mask = ((row + (col - c)) % 2 === 0);
                        if (mask) dark = !dark;
                        modules[row][col - c] = dark;
                        bitIndex--;
                        if (bitIndex === -1) {
                            byteIndex++;
                            bitIndex = 7;
                        }
                    }
                }
                row += inc;
                if (row < 0 || moduleCount <= row) {
                    row -= inc;
                    inc = -inc;
                    break;
                }
            }
        }

        return { modules: modules, count: moduleCount };
    }

    function renderToSvg(container, text, size) {
        if (!container) return;
        size = size || 220;
        const qr = buildMatrix(text);
        const margin = 4;
        const total = qr.count + margin * 2;
        let paths = '';
        for (let r = 0; r < qr.count; r++) {
            for (let c = 0; c < qr.count; c++) {
                if (qr.modules[r][c]) {
                    paths += 'M' + (c + margin) + ',' + (r + margin) + 'h1v1h-1z ';
                }
            }
        }
        container.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + total + ' ' + total + '" width="' + size + '" height="' + size + '" shape-rendering="crispEdges" style="display:block;margin:0 auto;border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">' +
            '<rect width="100%" height="100%" fill="#ffffff"/>' +
            '<path d="' + paths + '" fill="#0f172a"/>' +
            '</svg>';
    }

    function downloadPosterPng(text, eventTitle, campusName, filename) {
        const qr = buildMatrix(text);
        const canvas = document.createElement('canvas');
        const width = 680;
        const height = 840;
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // Background
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, width, height);

        // White Card
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.roundRect(40, 40, width - 80, height - 80, 24);
        ctx.fill();

        // Header Badge
        ctx.fillStyle = '#2563eb';
        ctx.beginPath();
        ctx.roundRect(190, 72, 300, 34, 17);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 13px Inter, Segoe UI, Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('SCAN QR CODE KUESIONER EVENT', width / 2, 94);

        // Event Title
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 22px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText(String(eventTitle || 'Event Kampus').slice(0, 42), width / 2, 142);

        // Campus Name
        ctx.fillStyle = '#475569';
        ctx.font = '600 15px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText(String(campusName || 'EventHub Integrated Scheduler').slice(0, 50), width / 2, 170);

        // Draw QR Modules
        const qrBoxSize = 440;
        const margin = 2;
        const totalModules = qr.count + margin * 2;
        const cellSize = qrBoxSize / totalModules;
        const startX = (width - qrBoxSize) / 2;
        const startY = 200;

        ctx.fillStyle = '#f8fafc';
        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.roundRect(startX - 16, startY - 16, qrBoxSize + 32, qrBoxSize + 32, 16);
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = '#0f172a';
        for (let r = 0; r < qr.count; r++) {
            for (let c = 0; c < qr.count; c++) {
                if (qr.modules[r][c]) {
                    const x = Math.floor(startX + (c + margin) * cellSize);
                    const y = Math.floor(startY + (r + margin) * cellSize);
                    const w = Math.ceil(cellSize);
                    const h = Math.ceil(cellSize);
                    ctx.fillRect(x, y, w, h);
                }
            }
        }

        // Footer Link Text
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 14px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('Atau buka melalui tautan berikut:', width / 2, 695);

        ctx.fillStyle = '#2563eb';
        ctx.font = '600 13px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText(String(text || '').slice(0, 68), width / 2, 722);

        ctx.fillStyle = '#64748b';
        ctx.font = '12px Inter, Segoe UI, Arial, sans-serif';
        ctx.fillText('Data otomatis terintegrasi dengan EventHub CRM Workspace', width / 2, 765);

        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filename || 'qr-kuesioner-event.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    global.EventHubQRCode = {
        renderSvg: renderToSvg,
        downloadPoster: downloadPosterPng
    };
})(window);

