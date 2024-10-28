$(document).ready(function () {
    // Navigation Link Clicks
    $('.nav-link').on('click', function (e) {
        e.preventDefault();
        $('.nav-link').removeClass('link-active');
        $(this).addClass('link-active');

        let url = $(this).attr('href');
        window.history.pushState({ path: url }, '', url);
    });

    // Dashboard Link Click
    $('#dashboard-link').on('click', function (e) {
        e.preventDefault();
        viewAnalytics();
    });

    // Products Link Click
    $('#products-link').on('click', function (e) {
        e.preventDefault();
        viewProducts();
    });

    // Accounts Link Click
    $('#accounts-link').on('click', function (e) {
        e.preventDefault();
        console.log("Accounts link clicked"); // Debug log
        viewAccounts();
    });

    // Default view on page load
    let url = window.location.href;
    if (url.endsWith('dashboard')) {
        $('#dashboard-link').trigger('click');
    } else if (url.endsWith('products')) {
        $('#products-link').trigger('click');
    } else if (url.endsWith('accounts')) {
        $('#accounts-link').trigger('click');
    } else {
        $('#dashboard-link').trigger('click');
    }

    // View Analytics Function
    function viewAnalytics() {
        $.ajax({
            type: 'GET',
            url: 'view-analytics.php',
            dataType: 'html',
            success: function (response) {
                $('.content-page').html(response);
                loadChart();
            }
        });
    }

    // Load Chart Function
    function loadChart() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Sales',
                    data: [7000, 5500, 5000, 4000, 4500, 6500, 8200, 8500, 9200, 9600, 10000, 9800],
                    backgroundColor: '#EE4C51',
                    borderColor: '#EE4C51',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10000,
                        ticks: {
                            stepSize: 2000  // Set step size to 2000
                        }
                    }
                }
            }
        });
    }

    // View Products Function
    function viewProducts() {
        $.ajax({
            type: 'GET',
            url: '../products/view-products.php',
            dataType: 'html',
            success: function (response) {
                $('.content-page').html(response);
                initializeProductsTable();
            }
        });
    }

    function initializeProductsTable() {
        var table = $('#table-products').DataTable({
            dom: 'rtp',
            pageLength: 10,
            ordering: false,
        });

        // Bind custom input to DataTable search
        $('#custom-search').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#category-filter').on('change', function () {
            if (this.value !== 'choose') {
                table.column(3).search(this.value).draw();
            }
        });

        $('#add-product').on('click', function (e) {
            e.preventDefault();
            addProduct();
        });
    }

    // Add Product Function
    function addProduct() {
        $.ajax({
            type: 'GET',
            url: '../products/add-product.html',
            dataType: 'html',
            success: function (view) {
                $('.modal-container').html(view);
                $('#staticBackdrop').modal('show');

                fetchCategories();

                $('#form-add-product').on('submit', function (e) {
                    e.preventDefault();
                    saveProduct();
                });
            }
        });
    }

    // Save Product Function
    function saveProduct() {
        $.ajax({
            type: 'POST',
            url: '../products/add-product.php',  // Make sure this points to your PHP handler
            data: $('#form-add-product').serialize(), // Serialize the form data
            dataType: 'json', // Expect a JSON response
            success: function (response) {
                if (response.status === 'error') {
                    handleProductErrors(response);
                } else if (response.status === 'success') {
                    $('#staticBackdrop').modal('hide');
                    $('#form-add-product')[0].reset();  // Reset the form
                    viewProducts();
                }
            }
        });
    }

    function handleProductErrors(response) {
        if (response.codeErr) {
            $('#code').addClass('is-invalid');
            $('#code').next('.invalid-feedback').text(response.codeErr).show();
        } else {
            $('#code').removeClass('is-invalid');
        }
        if (response.nameErr) {
            $('#name').addClass('is-invalid');
            $('#name').next('.invalid-feedback').text(response.nameErr).show();
        } else {
            $('#name').removeClass('is-invalid');
        }
        if (response.categoryErr) {
            $('#category').addClass('is-invalid');
            $('#category').next('.invalid-feedback').text(response.categoryErr).show();
        } else {
            $('#category').removeClass('is-invalid');
        }
        if (response.priceErr) {
            $('#price').addClass('is-invalid');
            $('#price').next('.invalid-feedback').text(response.priceErr).show();
        } else {
            $('#price').removeClass('is-invalid');
        }
    }

    // Fetch Categories Function
    function fetchCategories() {
        $.ajax({
            url: '../products/fetch-categories.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#category').empty().append('<option value="">--Select--</option>');
                $.each(data, function (index, category) {
                    $('#category').append($('<option>', {
                        value: category.id,
                        text: category.name
                    }));
                });
            }
        });
    }

    // View Accounts Function
    function viewAccounts() {
        $.ajax({
            type: 'GET',
            url: 'view-accounts.php',
            dataType: 'html',
            success: function (response) {
                $('.content-page').html(response);
                initializeAccountsTable();
            },
            error: function (xhr, status, error) {
                console.error("Error loading accounts:", error);
                $('.content-page').html('<p>Error loading accounts. Please try again.</p>');
            }
        });
    }

    // Initialize Accounts Table
    function initializeAccountsTable() {
        var table = $('#table-accounts').DataTable({
            dom: 'rtp',
            pageLength: 10,
            ordering: false,
        });

        // Add account button click
        $('#add-account-btn').on('click', function () {
            $('#modal-add-account').modal('show');
        });

        // Handle account form submission
        $('#form-add-account').on('submit', function (e) {
            e.preventDefault();
            addAccount();
        });
    }

    // Add Account Function
    function addAccount() {
        $.ajax({
            type: 'POST',
            url: 'add-account.php', // Make sure this points to your PHP handler
            data: $('#form-add-account').serialize(), // Serialize the form data
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    handleAccountErrors(response);
                } else if (response.status === 'success') {
                    $('#modal-add-account').modal('hide');
                    $('#form-add-account')[0].reset();  // Reset the form
                    viewAccounts(); // Refresh accounts view
                }
            },
            error: function (xhr, status, error) {
                console.error("Error adding account:", error);
            }
        });
    }

    function handleAccountErrors(response) {
        if (response.usernameErr) {
            $('#username').addClass('is-invalid');
            $('#username').next('.invalid-feedback').text(response.usernameErr).show();
        } else {
            $('#username').removeClass('is-invalid');
        }
        if (response.passwordErr) {
            $('#password').addClass('is-invalid');
            $('#password').next('.invalid-feedback').text(response.passwordErr).show();
        } else {
            $('#password').removeClass('is-invalid');
        }
        if (response.roleErr) {
            $('#role').addClass('is-invalid');
            $('#role').next('.invalid-feedback').text(response.roleErr).show();
        } else {
            $('#role').removeClass('is-invalid');
        }
    }

    // Initialize Accounts Table
    function initializeAccountsTable() {
        var table = $('#table-accounts').DataTable({
            dom: 'rtp',
            pageLength: 10,
            ordering: false,
        });

        // Bind custom input to DataTable search
        $('#custom-search').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Role filter dropdown change event
        $('#role-filter').on('change', function () {
            // If the value is empty, clear the search on the role column
            if (this.value === '') {
                table.column(4).search('').draw();
            } else {
                // Filter the table based on selected role
                table.column(4).search(this.value).draw();
            }
        });

        // Add account button click
        $('#add-account').on('click', function () {
            $('#modal-add-account').modal('show');
        });

        // Handle account form submission
        $('#form-add-account').on('submit', function (e) {
            e.preventDefault();
            addAccount();
        });
    }

});
