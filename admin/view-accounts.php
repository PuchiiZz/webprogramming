<?php
require_once '../classes/account.class.php';
session_start();
$accountObj = new Account();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Accounts</h4>
            </div>
        </div>
    </div>
    <div class="modal-container"></div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex justify-content-center align-items-center">
                            <form class="d-flex me-2">
                                <div class="input-group w-100">
                                    <input type="text" class="form-control form-control-light" id="custom-search"
                                        placeholder="Search accounts...">
                                    <span class="input-group-text bg-primary border-primary text-white brand-bg-color">
                                        <i class="bi bi-search"></i>
                                    </span>
                                </div>
                            </form>
                            <div class="d-flex align-items-center">
                                <label for="role-filter" class="me-2">Role</label>
                                <select id="role-filter" class="form-select">
                                    <option value="">All</option>
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                        </div>
                        <div class="page-title-right d-flex align-items-center">
                            <button id="add-account" class="btn btn-primary brand-bg-color" data-bs-toggle="modal"
                                data-bs-target="#addAccountModal">Add Account</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-accounts" class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">No.</th>
                                    <th>Username</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="account-data">
                                <?php
                                $i = 1;
                                $array = $accountObj->showAll();

                                foreach ($array as $arr) {
                                ?>
                                <tr>
                                    <td class="text-start"><?= $i ?></td>
                                    <td><?= htmlspecialchars($arr['username']) ?></td>
                                    <td><?= htmlspecialchars($arr['first_name']) ?></td>
                                    <td><?= htmlspecialchars($arr['last_name']) ?></td>
                                    <td><?= htmlspecialchars($arr['role']) ?></td>
                                    <td class="text-nowrap">
                                        <a href="../accounts/editaccount.php?id=<?= $arr['id'] ?>"
                                            class="btn btn-sm btn-outline-success me-1">Edit</a>
                                        <?php if (isset($_SESSION['account']['is_admin']) && $_SESSION['account']['is_admin']) { ?>
                                        <button class="btn btn-sm btn-outline-danger deleteBtn me-1"
                                            data-id="<?= $arr['id'] ?>"
                                            data-name="<?= htmlspecialchars($arr['username']) ?>">Delete</button>
                                        <?php } ?>
                                        <button class="btn btn-sm btn-outline-primary stockInOutBtn"
                                            data-id="<?= $arr['id'] ?>">Stock-In/Out</button>
                                    </td>

                                </tr>
                                <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addAccountModal" class="modal fade" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAccountForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">---Select---</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Add Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// jQuery Document Ready
$(document).ready(function() {
    // Initialize the modal with options to prevent closing
    $('#addAccountModal').modal({
        backdrop: 'static', // Prevent closing the modal on backdrop click
        keyboard: false // Prevent closing the modal with the Esc key
    });

    // Add Account Modal Submission
    $('#addAccountForm').on('submit', function(e) {
        e.preventDefault();
        addAccount();
    });

    // Function to add account
    function addAccount() {
        $.ajax({
            type: 'POST',
            url: 'add-account.php', // Ensure this points to your PHP handler
            data: $('#addAccountForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Clear the form and hide the modal
                    $('#addAccountForm')[0].reset();
                    $('#addAccountModal').modal('hide');

                    // Refresh the accounts table
                    refreshAccountsTable();
                } else {
                    // Handle validation errors
                    handleAccountErrors(response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error adding account:", error);
            }
        });
    }

    // Function to refresh accounts table
    function refreshAccountsTable() {
        $.ajax({
            url: 'get-accounts.php', // A separate PHP file to fetch account data
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#account-data').empty(); // Clear existing data

                data.forEach(function(account, index) {
                    $('#account-data').append(`
                        <tr>
                            <td class="text-start">${index + 1}</td>
                            <td>${account.username}</td>
                            <td>${account.first_name}</td>
                            <td>${account.last_name}</td>
                            <td>${account.role}</td>
                            <td class="text-nowrap">
                                <a href="../accounts/editaccount.php?id=${account.id}" class="btn btn-sm btn-outline-success me-1">Edit</a>
                                <button class="btn btn-sm btn-outline-danger deleteBtn" data-id="${account.id}" data-name="${account.username}">Delete</button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching accounts:", error);
            }
        });
    }

    // Function to handle account errors
    function handleAccountErrors(response) {
        if (response.usernameErr) {
            $('#username').addClass('is-invalid');
            $('#username').next('.invalid-feedback').text(response.usernameErr).show();
        } else {
            $('#username').removeClass('is-invalid');
        }
        if (response.firstNameErr) {
            $('#first_name').addClass('is-invalid');
            $('#first_name').next('.invalid-feedback').text(response.firstNameErr).show();
        } else {
            $('#first_name').removeClass('is-invalid');
        }
        if (response.lastNameErr) {
            $('#last_name').addClass('is-invalid');
            $('#last_name').next('.invalid-feedback').text(response.lastNameErr).show();
        } else {
            $('#last_name').removeClass('is-invalid');
        }
        if (response.roleErr) {
            $('#role').addClass('is-invalid');
            $('#role').next('.invalid-feedback').text(response.roleErr).show();
        } else {
            $('#role').removeClass('is-invalid');
        }
    }

    $(document).ready(function() {
        // Handle search input
        $('#custom-search').on('keyup', function() {
            let searchQuery = $(this).val();
            let roleFilter = $('#role-filter').val();
            fetchFilteredAccounts(searchQuery, roleFilter);
        });

        // Handle role filter change
        $('#role-filter').on('change', function() {
            let searchQuery = $('#custom-search').val();
            let roleFilter = $(this).val();
            fetchFilteredAccounts(searchQuery, roleFilter);
        });


        function fetchFilteredAccounts(searchQuery, roleFilter) {
            $.ajax({
                type: 'GET',
                url: 'get-accounts.php',
                data: {
                    search: searchQuery,
                    role: roleFilter
                },
                dataType: 'json',
                success: function(data) {
                    $('#account-data').empty(); // Clear existing data
                    data.forEach(function(account, index) {
                        $('#account-data').append(`
                        <tr>
                            <td class="text-start">${index + 1}</td>
                            <td>${account.username}</td>
                            <td>${account.first_name}</td>
                            <td>${account.last_name}</td>
                            <td>${account.role}</td>
                            <td class="text-nowrap">
                                <a href="../accounts/editaccount.php?id=${account.id}" class="btn btn-sm btn-outline-success me-1">Edit</a>
                                <button class="btn btn-sm btn-outline-danger deleteBtn" data-id="${account.id}" data-name="${account.username}">Delete</button>
                            </td>
                        </tr>
                    `);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching accounts:", error);
                }
            });
        }
    });

});
</script>