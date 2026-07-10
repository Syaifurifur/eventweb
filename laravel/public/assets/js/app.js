(function () {
    const shell = document.querySelector('[data-shell]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const roleSwitcher = document.querySelector('[data-role-switcher]');
    const roleLabel = document.querySelector('[data-role-label]');
    const searchInput = document.querySelector('[data-table-search]');
    const searchableTable = document.querySelector('[data-searchable-table]');
    const calendarDays = document.querySelectorAll('[data-calendar-day]');
    const calendarEventLinks = document.querySelectorAll('[data-calendar-event-link]');
    const rolePresetSelects = document.querySelectorAll('[data-role-preset-target]');
    const campusComboboxes = document.querySelectorAll('[data-campus-combobox]');
    const crmEventComboboxes = document.querySelectorAll('[data-crm-event-combobox]');
    const crmCheckAll = document.querySelector('[data-crm-check-all]');
    const crmRowChecks = document.querySelectorAll('[data-crm-row-check]');
    const crmSelectedCount = document.querySelector('[data-crm-selected-count]');
    const crmModal = document.querySelector('[data-crm-modal]');
    const crmModalOpen = document.querySelector('[data-crm-modal-open]');
    const crmModalCloseButtons = document.querySelectorAll('[data-crm-modal-close]');
    const crmLabelReasons = document.querySelectorAll('[data-crm-label-reason]');
    const calendarExportForms = document.querySelectorAll('[data-calendar-export-form]');
    const campusRoleSelects = document.querySelectorAll('[data-campus-role-select]');
    const passwordToggleButtons = document.querySelectorAll('[data-password-toggle]');
    const paginatedTables = document.querySelectorAll('[data-paginated-table]');
    const dismissDocumentNotificationLinks = document.querySelectorAll('[data-dismiss-document-notification]');
    const eventDateRangeToggles = document.querySelectorAll('[data-event-date-range-toggle]');

    if (menuToggle && shell) {
        menuToggle.addEventListener('click', () => {
            shell.classList.toggle('is-menu-open');
        });
    }

    if (roleSwitcher && roleLabel) {
        roleSwitcher.addEventListener('change', () => {
            roleLabel.textContent = roleSwitcher.value;
        });
    }

    if (searchInput && searchableTable) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            searchableTable.querySelectorAll('tbody tr').forEach((row) => {
                row.hidden = !row.textContent.toLowerCase().includes(query);
            });
        });
    }

    paginatedTables.forEach((table) => {
        const panel = table.closest('.panel') || table.parentElement;
        const search = panel?.querySelector('[data-table-filter]');
        const summary = panel?.querySelector('[data-table-summary]');
        const pagination = panel?.querySelector('[data-table-pagination]');
        const rows = Array.from(table.querySelectorAll('tbody tr[data-page-row]'));
        const emptyRow = table.querySelector('tbody tr[data-empty-row]');
        const noMatchRow = document.createElement('tr');
        const noMatchCell = document.createElement('td');
        const pageSize = Math.max(1, Number.parseInt(table.dataset.pageSize || '5', 10));
        let currentPage = 1;

        noMatchRow.hidden = true;
        noMatchRow.dataset.tableNoMatch = 'true';
        noMatchCell.colSpan = table.querySelectorAll('thead th').length || 1;
        noMatchCell.textContent = 'Tidak ada data yang cocok.';
        noMatchRow.append(noMatchCell);
        table.querySelector('tbody')?.append(noMatchRow);

        const makeButton = (label, page, isActive = false, isDisabled = false) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `ghost-button compact-button${isActive ? ' is-active' : ''}`;
            button.textContent = label;
            button.disabled = isDisabled;
            button.addEventListener('click', () => {
                currentPage = page;
                render();
            });

            return button;
        };

        const render = () => {
            const query = (search?.value || '').trim().toLowerCase();
            const filtered = rows.filter((row) => {
                const searchableText = row.dataset.searchText || row.textContent;

                return searchableText.toLowerCase().includes(query);
            });
            const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);
            const start = (currentPage - 1) * pageSize;
            const visibleRows = new Set(filtered.slice(start, start + pageSize));

            rows.forEach((row) => {
                row.hidden = !visibleRows.has(row);
            });

            if (emptyRow) {
                emptyRow.hidden = rows.length > 0;
            }

            noMatchRow.hidden = rows.length === 0 || filtered.length > 0;

            if (summary) {
                const from = filtered.length === 0 ? 0 : start + 1;
                const to = Math.min(start + pageSize, filtered.length);
                summary.textContent = `${from}-${to} dari ${filtered.length} data`;
            }

            if (!pagination) {
                return;
            }

            pagination.replaceChildren();
            pagination.hidden = filtered.length <= pageSize;

            if (filtered.length <= pageSize) {
                return;
            }

            pagination.append(
                makeButton('Sebelumnya', Math.max(1, currentPage - 1), false, currentPage === 1)
            );

            const pages = document.createElement('div');
            pages.className = 'pagination-pages';

            for (let page = 1; page <= totalPages; page += 1) {
                if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) {
                    pages.append(makeButton(String(page), page, page === currentPage));
                } else if (Math.abs(page - currentPage) === 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    pages.append(ellipsis);
                }
            }

            pagination.append(
                pages,
                makeButton('Berikutnya', Math.min(totalPages, currentPage + 1), false, currentPage === totalPages)
            );
        };

        search?.addEventListener('input', () => {
            currentPage = 1;
            render();
        });

        render();
    });

    dismissDocumentNotificationLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const item = link.closest('[data-document-notification]');
            const panel = link.closest('[data-document-notification-panel]');
            item?.remove();
            const remaining = panel?.querySelectorAll('[data-document-notification]').length || 0;
            const count = panel?.querySelector('[data-document-notification-count]');

            if (count) {
                count.textContent = `${remaining} notifikasi`;
            }

            if (panel && remaining === 0) {
                panel.remove();
            }
        });
    });

    eventDateRangeToggles.forEach((toggle) => {
        const form = toggle.closest('form');
        const startInput = form?.querySelector('input[name="event_date"]');
        const startLabel = form?.querySelector('[data-event-date-start-label]');
        const endField = form?.querySelector('[data-event-date-range-end]');
        const endInput = endField?.querySelector('input[name="event_end_date"]');

        if (!endField || !endInput) {
            return;
        }

        const syncDateRange = () => {
            const enabled = toggle.checked;
            endField.style.display = enabled ? 'grid' : '';
            endInput.required = enabled;

            if (startLabel) {
                startLabel.textContent = enabled ? 'Tanggal Pelaksanaan Awal' : 'Tanggal Pelaksanaan';
            }

            if (startInput) {
                endInput.min = startInput.value || '';
            }

            if (!enabled) {
                endInput.value = '';
            } else if (startInput?.value && (endInput.value === '' || endInput.value < startInput.value)) {
                endInput.value = startInput.value;
            }
        };

        toggle.addEventListener('change', syncDateRange);
        startInput?.addEventListener('change', syncDateRange);
        form?.addEventListener('submit', syncDateRange);
        syncDateRange();
    });

    calendarDays.forEach((day) => {
        day.addEventListener('click', (event) => {
            if (event.target.closest('[data-calendar-event-link]')) {
                return;
            }

            if (event.target.closest('.calendar-event.google')) {
                calendarDays.forEach((item) => item.classList.remove('is-selected'));
                day.classList.add('is-selected');
                return;
            }

            if (day.dataset.calendarDetailUrl) {
                window.location.href = day.dataset.calendarDetailUrl;
                return;
            }

            calendarDays.forEach((item) => item.classList.remove('is-selected'));
            day.classList.add('is-selected');
        });
    });

    calendarEventLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    const shiftMonth = (value, offset) => {
        if (!/^\d{4}-\d{2}$/.test(value)) {
            return '';
        }

        const [year, month] = value.split('-').map(Number);
        const date = new Date(year, month - 1 + offset, 1);

        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    };

    calendarExportForms.forEach((form) => {
        const startInput = form.querySelector('input[name="start_month"]');
        const endInput = form.querySelector('input[name="end_month"]');

        if (!startInput || !endInput) {
            return;
        }

        const syncRangeLimit = () => {
            const start = startInput.value;
            if (!/^\d{4}-\d{2}$/.test(start)) {
                return;
            }

            const maxEnd = shiftMonth(start, 11);
            endInput.min = start;
            endInput.max = maxEnd;

            if (endInput.value === '' || endInput.value < start) {
                endInput.value = start;
            }

            if (maxEnd !== '' && endInput.value > maxEnd) {
                endInput.value = maxEnd;
            }
        };

        startInput.addEventListener('change', syncRangeLimit);
        endInput.addEventListener('change', syncRangeLimit);
        form.addEventListener('submit', syncRangeLimit);
        syncRangeLimit();
    });

    rolePresetSelects.forEach((select) => {
        select.addEventListener('change', () => {
            const target = select.dataset.rolePresetTarget;
            const scope = document.querySelector(`[data-access-scope="${target}"]`);
            const option = select.options[select.selectedIndex];

            if (!scope || !option) {
                return;
            }

            let permissions = [];
            try {
                permissions = JSON.parse(option.dataset.permissions || '[]');
            } catch (error) {
                permissions = [];
            }

            scope.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = permissions.includes(checkbox.value);
            });
        });
    });

    const syncCampusRoleField = (select) => {
        const scope = select.closest('.pending-user-card, .row-edit-panel, .drawer-form, form') || document;
        const field = scope.querySelector('[data-campus-role-field]');
        const campusSelect = field?.querySelector('select[name="campus_id"]');

        if (!field || !campusSelect) {
            return;
        }

        const selectedRole = select.value.trim().toLowerCase();
        const needsCampus = ['kepala kampus', 'markom', 'marketing akademik'].includes(selectedRole);
        field.hidden = !needsCampus;
        campusSelect.disabled = !needsCampus;
        campusSelect.required = needsCampus;

        if (!needsCampus) {
            campusSelect.value = '';
        }
    };

    campusRoleSelects.forEach((select) => {
        select.addEventListener('change', () => syncCampusRoleField(select));
        syncCampusRoleField(select);
    });

    passwordToggleButtons.forEach((button) => {
        const field = button.closest('.password-field');
        const input = field?.querySelector('input[type="password"], input[type="text"]');

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.classList.toggle('is-visible', isHidden);
            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Lihat password');
        });
    });

    campusComboboxes.forEach((combo) => {
        const input = combo.querySelector('input[list]');
        const hidden = combo.querySelector('input[type="hidden"]');
        const options = Array.from(combo.querySelectorAll('datalist option'));

        if (!input || !hidden) {
            return;
        }

        const syncCampusId = () => {
            const query = input.value.trim().toLowerCase();
            if (query === '') {
                hidden.value = '';
                return;
            }

            const selected = options.find((option) => option.value.toLowerCase() === query)
                || options.find((option) => (option.dataset.id || '').toLowerCase() === query)
                || (() => {
                    const matches = options.filter((option) => option.value.toLowerCase().includes(query));
                    return matches.length === 1 ? matches[0] : null;
                })();

            hidden.value = selected ? selected.dataset.id || '' : '';
        };

        input.addEventListener('input', syncCampusId);
        input.addEventListener('change', syncCampusId);
        input.form?.addEventListener('submit', syncCampusId);
    });

    crmEventComboboxes.forEach((combo) => {
        const input = combo.querySelector('[data-crm-event-search]');
        const hidden = combo.querySelector('[data-crm-event-id]');
        const suggestions = combo.querySelector('[data-crm-event-suggestions]');
        const emptyState = combo.querySelector('[data-crm-event-empty]');
        const options = Array.from(combo.querySelectorAll('[data-crm-event-option]'));

        if (!input || !hidden || !suggestions) {
            return;
        }

        const setSelectedEvent = (option) => {
            input.value = option.dataset.label || option.textContent.trim();
            hidden.value = option.dataset.id || '';
            input.setCustomValidity('');
            suggestions.hidden = true;
        };

        const normalizeSearchText = (value) => value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();

        const tokenScore = (queryToken, candidateTokens, candidateText) => {
            if (candidateText.includes(queryToken)) {
                return 18;
            }

            let score = 0;
            candidateTokens.forEach((candidateToken) => {
                if (candidateToken === queryToken) {
                    score = Math.max(score, 40);
                } else if (candidateToken.startsWith(queryToken)) {
                    score = Math.max(score, 30);
                } else if (candidateToken.includes(queryToken)) {
                    score = Math.max(score, 22);
                } else if (queryToken.length >= 3 && candidateToken.includes(queryToken.slice(0, -1))) {
                    score = Math.max(score, 12);
                }
            });

            return score;
        };

        const matchingOptions = () => {
            const query = normalizeSearchText(input.value);

            if (query === '') {
                return [];
            }

            const queryTokens = query.split(/\s+/).filter(Boolean);

            return options
                .map((option) => {
                    const candidateText = normalizeSearchText(option.dataset.search || option.dataset.label || '');
                    const candidateTokens = candidateText.split(/\s+/).filter(Boolean);
                    const phraseBonus = candidateText.includes(query) ? 50 : 0;
                    const tokenScores = queryTokens.map((queryToken) => tokenScore(queryToken, candidateTokens, candidateText));

                    return {
                        option,
                        score: phraseBonus + tokenScores.reduce((total, score) => total + score, 0),
                        matchedTokens: tokenScores.filter((score) => score > 0).length,
                    };
                })
                .filter((result) => result.matchedTokens === queryTokens.length)
                .sort((a, b) => b.score - a.score)
                .map((result) => result.option)
                .slice(0, 8);
        };

        const renderSuggestions = () => {
            const matches = matchingOptions();
            const hasQuery = input.value.trim() !== '';

            options.forEach((option) => {
                const isMatch = matches.includes(option);
                option.hidden = !isMatch;
                option.style.display = isMatch ? '' : 'none';
            });
            matches.forEach((option) => {
                suggestions.insertBefore(option, emptyState || null);
            });

            if (emptyState) {
                emptyState.hidden = !hasQuery || matches.length > 0;
            }

            suggestions.hidden = !hasQuery || (matches.length === 0 && !emptyState);
        };

        const syncEventId = () => {
            const query = input.value.trim().toLowerCase();
            input.setCustomValidity('');

            if (query === '') {
                hidden.value = '';
                renderSuggestions();
                return null;
            }

            const exact = options.find((option) => (option.dataset.label || '').toLowerCase() === query);
            const selected = exact || null;
            hidden.value = selected ? selected.dataset.id || '' : '';
            renderSuggestions();

            return selected;
        };

        options.forEach((option) => {
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                setSelectedEvent(option);
            });
        });

        input.addEventListener('input', syncEventId);
        input.addEventListener('change', syncEventId);
        input.addEventListener('focus', renderSuggestions);
        input.addEventListener('blur', () => {
            window.setTimeout(() => {
                suggestions.hidden = true;
            }, 120);
        });
        input.form?.addEventListener('submit', (event) => {
            if (syncEventId()) {
                return;
            }

            input.setCustomValidity('Pilih event dari hasil pencarian.');
            input.reportValidity();
            event.preventDefault();
        });
        syncEventId();
    });

    const updateCrmSelectedCount = () => {
        if (!crmSelectedCount) {
            return;
        }

        const checked = Array.from(crmRowChecks).filter((checkbox) => checkbox.checked).length;
        crmSelectedCount.textContent = `${checked} dipilih`;

        if (crmCheckAll) {
            crmCheckAll.checked = checked > 0 && checked === crmRowChecks.length;
            crmCheckAll.indeterminate = checked > 0 && checked < crmRowChecks.length;
        }
    };

    if (crmCheckAll) {
        crmCheckAll.addEventListener('change', () => {
            crmRowChecks.forEach((checkbox) => {
                checkbox.checked = crmCheckAll.checked;
            });
            updateCrmSelectedCount();
        });
    }

    crmRowChecks.forEach((checkbox) => {
        checkbox.addEventListener('change', updateCrmSelectedCount);
    });
    updateCrmSelectedCount();

    crmLabelReasons.forEach((field) => {
        const formId = field.dataset.crmLeadForm;
        const stage = document.querySelector(`[data-crm-stage-select][data-crm-lead-form="${formId}"]`);
        const label = document.querySelector(`[data-crm-label-select][data-crm-lead-form="${formId}"]`);

        if (!stage || !label) {
            return;
        }

        const syncLabelReason = () => {
            const active = ['Tahap 2', 'Tahap 3'].includes(stage.value)
                && ['Minat/Tertarik', 'Ragu-ragu', 'Menolak'].includes(label.value);
            field.disabled = !active;
            field.classList.toggle('is-active', active);
        };

        stage.addEventListener('change', syncLabelReason);
        label.addEventListener('change', syncLabelReason);
        syncLabelReason();
    });

    if (crmModal && crmModalOpen) {
        const openModal = () => {
            crmModal.hidden = false;
            document.body.classList.add('is-crm-modal-open');
            crmModal.querySelector('input, select, button')?.focus();
        };

        const closeModal = () => {
            crmModal.hidden = true;
            document.body.classList.remove('is-crm-modal-open');
            crmModalOpen.focus();
        };

        crmModalOpen.addEventListener('click', openModal);
        crmModalCloseButtons.forEach((button) => {
            button.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !crmModal.hidden) {
                closeModal();
            }
        });
    }
}());
