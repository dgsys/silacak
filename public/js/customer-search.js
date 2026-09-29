(() => {
    const lookup = document.querySelector('[data-customer-search]');
    if (!lookup) return;

    const input = lookup.querySelector('#customer_search');
    const customerId = lookup.querySelector('#customer_id');
    const results = lookup.querySelector('#customer_search_results');
    const status = lookup.querySelector('#customer_search_status');
    const clearButton = lookup.querySelector('#customer_search_clear');
    let debounce;
    let activeRequest;

    const closeResults = () => {
        results.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };

    const selectCustomer = (customer) => {
        window.clearTimeout(debounce);
        activeRequest?.abort();
        customerId.value = customer.id;
        input.value = `${customer.nama} (${customer.telepon})${customer.is_member ? ' - Member' : ''}`;
        input.readOnly = true;
        clearButton.hidden = false;
        status.textContent = 'Pelanggan dipilih.';
        closeResults();
    };

    const showResults = (customers) => {
        results.replaceChildren();
        if (customers.length === 0) {
            status.textContent = 'Pelanggan tidak ditemukan.';
            closeResults();
            return;
        }

        customers.forEach((customer) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'customer-search-option';
            option.setAttribute('role', 'option');
            option.textContent = `${customer.nama} (${customer.telepon})${customer.is_member ? ' - Member' : ''}`;
            option.addEventListener('click', () => selectCustomer(customer));
            results.append(option);
        });

        status.textContent = customers.length === 20
            ? 'Menampilkan 20 hasil pertama. Perjelas kata kunci untuk hasil lain.'
            : `${customers.length} pelanggan ditemukan.`;
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const search = async (keyword) => {
        const endpoint = new URL(lookup.dataset.searchUrl, window.location.origin);
        endpoint.searchParams.set('q', keyword);
        const controller = new AbortController();
        activeRequest = controller;

        try {
            const response = await fetch(endpoint, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Pencarian gagal. Coba lagi.');

            const customers = await response.json();
            if (input.value.trim() === keyword && !input.readOnly) showResults(customers);
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = error.message;
                closeResults();
            }
        }
    };

    input.addEventListener('input', () => {
        customerId.value = '';
        clearButton.hidden = true;
        window.clearTimeout(debounce);
        activeRequest?.abort();

        const keyword = input.value.trim();
        if (keyword.length < 2) {
            status.textContent = 'Ketik minimal 2 karakter untuk mencari.';
            closeResults();
            return;
        }

        status.textContent = 'Mencari pelanggan...';
        debounce = window.setTimeout(() => search(keyword), 250);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && !results.hidden) {
            event.preventDefault();
            results.querySelector('button')?.focus();
        } else if (event.key === 'Enter' && !results.hidden) {
            event.preventDefault();
            results.querySelector('button')?.click();
        } else if (event.key === 'Escape') {
            closeResults();
        }
    });

    results.addEventListener('keydown', (event) => {
        const options = [...results.querySelectorAll('button')];
        const currentIndex = options.indexOf(document.activeElement);
        if (event.key === 'ArrowDown' && currentIndex < options.length - 1) {
            event.preventDefault();
            options[currentIndex + 1].focus();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (currentIndex > 0) options[currentIndex - 1].focus();
            else input.focus();
        } else if (event.key === 'Escape') {
            closeResults();
            input.focus();
        }
    });

    clearButton.addEventListener('click', () => {
        customerId.value = '';
        input.value = '';
        input.readOnly = false;
        clearButton.hidden = true;
        status.textContent = 'Ketik minimal 2 karakter untuk mencari.';
        input.focus();
    });

    if (customerId.value) {
        input.readOnly = true;
        clearButton.hidden = false;
    }
})();