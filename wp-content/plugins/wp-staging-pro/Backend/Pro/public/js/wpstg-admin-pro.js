"use strict";

var WPStagingPro = function ($) {

    var that = {
        isCancelled: false,
        isFinished: false,
        getLogs: false,
    };

    // Cache Elements
    var cache = {elements: []};

    /**
     * Get / Set Cache for Selector
     * @param {String} selector
     * @returns {*}
     */
    cache.get = function (selector)
    {
        // It is already cached!
        if ($.inArray(selector, cache.elements) !== -1)
        {
            //console.log('already cached' + cache.elements[selector]);
            return cache.elements[selector];
        }

        // Create cache and return
        cache.elements[selector] = jQuery(selector);

        //console.log(cache.elements[selector]);
        return cache.elements[selector];
    };

    /**
     * Refreshes given cache
     * @param {String} selector
     */
    cache.refresh = function (selector)
    {
        selector.elements[selector] = jQuery(selector);
    };


    /**
     * Ajax Scanning before starting push process
     */
    var startScanning = function ()
    {
        // Scan db and file system
        console.log('Loading WP STAGING Pro Initially');
        var $workFlow = cache.get("#wpstg-workflow");


        $workFlow
                // Load scanning data
                .on("click", ".wpstg-push-changes", function (e) {
                    e.preventDefault();

                    var $this = $(this)

                    // Disable button
                    if ($this.attr("disabled"))
                    {
                        return false;
                    }

                    // Add loading overlay
                    $workFlow.addClass("loading");

                    // Get clone id
                    //var cloneID = $this.data("clone");
                    // Get clone id
                    var cloneID = $(this).data("clone");
                    console.log('Clone ID: ' + cloneID);

                    // Prepare data
                    that.data = {
                        action: 'wpstg_scan',
                        clone: cloneID,
                        accessToken: wpstg.accessToken,
                        nonce: wpstg.nonce
                    };

                    // Send ajax request
                    WPStaging.ajax(
                            that.data,
                            function (response) {

                                if (response.length < 1)
                                {
                                    showError(
                                            "Something went wrong! No response.  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                                            "and try again. If that does not help, " +
                                            "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                            );
                                }

                                // Styling of elements
                                $workFlow.removeClass("loading").html(response);

                                WPStaging.switchStep(2);

                                cache.get(".wpstg-step3-cloning").hide();
                                cache.get(".wpstg-step3-pushing").show();

                                cache.get(".wpstg-loader").hide();
                                //cache.get(".wpstg-loader").hide();

                            },
                            "HTML"
                            );
                })
                // Previous Button
                .on("click", ".wpstg-prev-step-link", function (e) {
                    e.preventDefault();
                    WPStaging.loadOverview();
                })
                .on("click", "#wpstg-use-target-dir", function (e) {
                    e.preventDefault();
                    $('#wpstg_clone_dir').val(this.getAttribute('data-path'));

                })
                .on("click", "#wpstg-use-target-hostname", function (e) {
                    e.preventDefault();
                    $('#wpstg_clone_hostname').val(this.getAttribute('data-uri'));
                })
                .on("change", "#wpstg-delete-upload-before-pushing", function (e) {
                    if (e.currentTarget.checked) {
                        $('#wpstg-backup-upload-container').show();
                    } else {
                        $('#wpstg-backup-upload-container').hide();
                        $('#wpstg-backup-upload-before-pushing').removeAttr('checked');
                    }
                })
    };


    // Start the whole pushing process
    var startProcess = function () {
        var $workFlow = cache.get("#wpstg-workflow");

        // Click push changes button
        $workFlow.on("click", "#wpstg-push-changes", function (e) {
            e.preventDefault();

            // Hide db tables and folder selection
            cache.get('.wpstg-tabs-wrapper').hide();
            cache.get('#wpstg-push-changes').hide();

            // Show confirmation modal
            var cloneID = cache.get('#wpstg-push-changes').data("clone");
            var text = "WAIT! This will overwrite the production/live site and its plugins, themes and media assets with data from the staging site: \"" + cloneID + "\".  \n\nDatabase data will be overwritten for each selected table. Take care if you use a shop system like WooCommerce and check out our FAQ! \n\nIMPORTANT: Before you proceed make sure that you have a full site backup. If the pushing process is not successful contact us at support@wp-staging.com or use the REPORT ISSUE button.";

            if (confirmModal(text)) {
                cache.get('#wpstg-push-changes').attr('disabled', true);
                cache.get('.wpstg-prev-step-link').attr('disabled', true);
                cache.get('#wpstg-scanning-files').hide();
                cache.get('.wpstg-progress-bar-wrapper').show();
                WPStaging.switchStep(3);
                window.addEventListener('beforeunload', wpstgWarnIfClose);
                processing();
            }
        });
    };

    /**
     * Start ajax processing
     * @returns string
     */
    var processing = function () {
        console.log("Start ajax processing");

        // Show loader gif
        cache.get(".wpstg-loader").show();

        // Show logging window
        cache.get('.wpstg-log-details').show();

        // Get clone id
        var cloneID = cache.get('#wpstg-push-changes').data("clone");
        console.log(cloneID);

        var deleteUploadsBeforePush = cache.get('#wpstg-delete-upload-before-pushing')[0].checked;
        var backupUploadsBeforePush = false;
        if (deleteUploadsBeforePush) {
            backupUploadsBeforePush = cache.get('#wpstg-backup-upload-before-pushing')[0].checked;
        }

        WPStaging.ajax(
                {
                    action: "wpstg_push_processing",
                    accessToken: wpstg.accessToken,
                    nonce: wpstg.nonce,
                    clone: cloneID,
                    excludedTables: getExcludedTables(),
                    includedDirectories: getIncludedDirectories(),
                    excludedDirectories: getExcludedDirectories(),
                    extraDirectories: getIncludedExtraDirectories(),
                    createSnapshotBeforePushing: cache.get('#wpstg-create-snapshot-before-pushing')[0].checked,
                    deletePluginsAndThemes: cache.get('#wpstg-remove-uninstalled-plugins-themes')[0].checked,
                    deleteUploadsBeforePushing: deleteUploadsBeforePush,
                    backupUploadsBeforePushing: backupUploadsBeforePush,
                },
        function (response)
        {
            // Undefined Error
            if (false === response)
            {
                showError(
                        "Something went wrong! Error: No response.  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                        "and try again. If that does not help, " +
                        "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                        );
                cache.get(".wpstg-loader").hide();
                window.removeEventListener('beforeunload', wpstgWarnIfClose);
                return;
            }

            // Throw Error
            if ("undefined" !== typeof (response.error) && response.error) {
                console.log(response.message);
                WPStaging.showError(
                        "Something went wrong! Error: " + response.message + ".  Go to WP Staging > Settings and lower 'File Copy Limit' and 'DB Query Limit'. Also set 'CPU Load Priority to low '" +
                        "and try again. If that does not help, " +
                        "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                        );
                window.removeEventListener('beforeunload', wpstgWarnIfClose);
                return;
            }

            // Add Log messages
            if ("undefined" !== typeof (response.last_msg) && response.last_msg)
            {
                WPStaging.getLogs(response.last_msg);
            }

            // Continue processing
            if (false === response.status)
            {
                progressBar(response);
                setTimeout(function () {
                    console.log('continue processing');
                    cache.get(".wpstg-loader").show();
                    processing();
                }, wpstg.delayReq);

            } else if (true === response.status) {
                progressBar(response);
                console.log('Processing....');
                processing();
            } else if ('finished' === response.status || ("undefined" !== typeof (response.job_done) && response.job_done)) {
                window.removeEventListener('beforeunload', wpstgWarnIfClose);
                isFinished(response);
            }
            ;
        },
                "json",
                false
                );
    };

    /**
     * Test database connection
     * @returns object
     */
    var connectDatabase = function () {
        var $workFlow = cache.get("#wpstg-workflow");
        $workFlow.on("click", "#wpstg-db-connect", function (e) {
            e.preventDefault();
            console.log(this);
            cache.get(".wpstg-loader").show();
            cache.get('#wpstg-db-status').hide();
            WPStaging.ajax(
                    {
                        action: "wpstg_database_connect",
                        accessToken: wpstg.accessToken,
                        nonce: wpstg.nonce,
                        databaseUser: cache.get('#wpstg_db_username').val(),
                        databasePassword: cache.get('#wpstg_db_password').val(),
                        databaseServer: cache.get('#wpstg_db_server').val(),
                        databaseDatabase: cache.get('#wpstg_db_database').val(),
                        databasePrefix: cache.get('#wpstg_db_prefix').val(),
                    },
                    function (response)
                    {
                        // Undefined Error
                        if (false === response)
                        {
                            showError(
                                    "Something went wrong! Error: No response." +
                                    "Please try again. If that does not help, " +
                                    "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                    );
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').remove();
                            cache.get('#wpstg-error-details').hide();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-failed"> Failed</span>');
                            return;
                        }

                        // Throw Error
                        if ("undefined" !== typeof (response.errors) && response.errors) {
                            //console.log(response.errors);
                            WPStaging.showError(
                                    "Something went wrong! Error: " + response.errors +
                                    " Please try again. If that does not help, " +
                                    "<a href='https://wp-staging.com/support/' target='_blank'>open a support ticket</a> "
                                    );
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').hide();
                            cache.get('#wpstg-db-error').remove();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-failed"> Failed</span><br/><span id="wpstg-db-error" style="color:red;">Error: '+ response.errors +'</span>');
                            return;
                        }

                        if ("undefined" !== typeof (response.success) && response.success) {
                            cache.get(".wpstg-loader").hide();
                            cache.get('#wpstg-db-status').hide();
                            cache.get('#wpstg-error-details').hide();
                            cache.get('#wpstg-db-error').hide();
                            cache.get('#wpstg-db-connect').after('<span id="wpstg-db-status" class="wpstg-success"> Success</span>');
                        }

                    },
                    "json",
                    false
                    );

        });

        // Make form fields editable
        $workFlow.on("click", "#wpstg-ext-db", function (e) {
            if (this.checked) {
                cache.get("#wpstg_db_server").removeAttr('readonly');
                cache.get("#wpstg_db_username").removeAttr('readonly');
                cache.get("#wpstg_db_password").removeAttr('readonly');
                cache.get("#wpstg_db_database").removeAttr('readonly');
                cache.get("#wpstg_db_prefix").removeAttr('readonly');
            } else {
                cache.get("#wpstg_db_server").attr('readonly', true).val('');
                cache.get("#wpstg_db_username").attr('readonly', true).val('');
                cache.get("#wpstg_db_password").attr('readonly', true).val('');
                cache.get("#wpstg_db_database").attr('readonly', true).val('');
                cache.get("#wpstg_db_prefix").attr('readonly', true).val('');
            }

        });

    };

    var editCloneData = function () {
        // Scan db and file system
        var $workFlow = cache.get("#wpstg-workflow");

        $workFlow
            // Load scanning data
            .on("click", ".wpstg-edit-clone-data", function (e) {
                e.preventDefault();

                var $this = $(this)

                // Disable button
                if ($this.attr("disabled")) {
                    return false;
                }

                // Get clone id
                var cloneID = $(this).data("clone");

                // Prepare data
                that.data = {
                    action: 'wpstg_edit_clone_data',
                    clone: cloneID,
                    accessToken: wpstg.accessToken,
                    nonce: wpstg.nonce
                };

                // Send ajax request
                WPStaging.ajax(
                    that.data,
                    function (response) {
                        $workFlow.html(response);
                    },
                    "HTML"
                );
            })
            .on("click", ".wpstg-prev-step-link", function (e) {
                e.preventDefault();
                WPStaging.loadOverview();
            })
            .on("click", "#wpstg-save-clone-data", function (e) {
                e.preventDefault();

                var idPrefix = "#wpstg-edit-clone-data-";
                // Get clone id
                //var cloneID = $this.data("clone");
                // Get clone id
                var cloneID = cache.get(idPrefix + "clone-id").val();

                var directoryName = cache.get(idPrefix + "directory-name").val();
                var path = cache.get(idPrefix + "path").val();
                var url = cache.get(idPrefix + "url").val();
                var prefix = cache.get(idPrefix + "prefix").val();
                var externalDBUser = cache.get(idPrefix + "database-user").val();
                var externalDBPassword = cache.get(idPrefix + "database-password").val();
                var externalDBDatabase = cache.get(idPrefix + "database-database").val();
                var externalDBHost = cache.get(idPrefix + "database-server").val();
                var externalDBPrefix = cache.get(idPrefix + "database-prefix").val();

                // Prepare data
                that.data = {
                    action: 'wpstg_save_clone_data',
                    clone: cloneID,
                    directoryName: directoryName,
                    path: path,
                    url: url,
                    prefix: prefix,
                    externalDBUser: externalDBUser,
                    externalDBPassword: externalDBPassword,
                    externalDBDatabase: externalDBDatabase,
                    externalDBHost: externalDBHost,
                    externalDBPrefix: externalDBPrefix,
                    accessToken: wpstg.accessToken,
                    nonce: wpstg.nonce
                };

                WPStaging.ajax(
                    that.data,
                    function (response) {
                        if (response === "Success") {
                            WPStaging.loadOverview();
                        } else {
                            alert(response);
                        }
                    },
                    "HTML"
                );
            });
    }

    /**
     * All jobs are finished
     * @param {object} response
     * @returns object
     */
    var isFinished = function (response) {
        progressBar(response);
        console.log('result: ' + response);
        console.log('Finishing .... result: ' + response);
        cache.get(".wpstg-loader").text('Finished');
        cache.get(".wpstg-loader").addClass('wpstg-finished');
        cache.get('.wpstg-prev-step-link').attr('disabled', false);
        Swal.fire(
            'Pushing successful!',
            /*'Go to <a href="options-permalink.php">Permalinks</a> and save them again. <br>'+*/'Delete site cache if required!',
            'success'
        );
    };

    /**
     * Get Excluded (Unchecked) Database Tables
     * @returns {Array}
     */
    var getExcludedTables = function ()
    {
        var excludedTables = [];

        $(".wpstg-db-table input:not(:checked)").each(function () {
            excludedTables.push(this.name);
        });

        return excludedTables;
    };



    /**
     * A confirmation modal
     *
     * @param string
     * @returns {Boolean}
     */
    var confirmModal = function (string) {
        var check = confirm(string);

        if (check === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Included Directories
     * @returns {Array}
     */
    var getIncludedDirectories = function ()
    {
        var includedDirectories = [];

        $(".wpstg-dir input:checked").each(function () {
            var $this = $(this);
            if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled"))
            {
                includedDirectories.push($this.val());
            }
        });

        return includedDirectories;
    };

    /**
     * Get Excluded Directories
     * @returns {Array}
     */
    var getExcludedDirectories = function ()
    {
        var excludedDirectories = [];

        $(".wpstg-dir input:not(:checked)").each(function () {
            var $this = $(this);
            //if (!$this.parent(".wpstg-dir").parents(".wpstg-dir").children(".wpstg-expand-dirs").hasClass("disabled"))
            //{
            excludedDirectories.push($this.val());
            //}
        });

        return excludedDirectories;
    };

    /**
     * Get Included Extra Directories
     * @returns {Array}
     */
    var getIncludedExtraDirectories = function ()
    {
        var extraDirectories = [];

        if (!$("#wpstg_extraDirectories").val()) {
            return extraDirectories;
        }

        var extraDirectories = $("#wpstg_extraDirectories").val().split(/\r?\n/);
        //console.log(extraDirectories);

        //excludedDirectories.push($this.val());

        return extraDirectories;
    };

    var progressBar = function (response, restart) {
        if ("undefined" === typeof (response.percentage))
            return false;

        if (response.job === 'JobCreateSnapshot') {
            cache.get("#wpstg-progress-backup").width(response.percentage * 0.15 + '%').html(response.percentage + '%');
            cache.get("#wpstg-processing-status").html(response.percentage.toFixed(0) + '%' + ' - Step 1 of 4 Creating backup...');
        }

        if (response.job === 'jobFileScanning' || response.job === 'jobCopy') {
            cache.get("#wpstg-progress-backup").css('background-color', '#3bc36b');
            cache.get("#wpstg-progress-backup").html('1. Backup');
            //Assumption: All previous steps are done.
            //This avoids bugs where some steps are skipped and the progress bar is incomplete as a result
            cache.get("#wpstg-progress-backup").width('15%');

            let percentage = 0;
            if (response.job === 'jobFileScanning') {
                percentage = response.percentage / 2;
            }
            else {
                percentage = 50 + response.percentage / 2;
            }

            cache.get("#wpstg-progress-files").width(percentage * 0.3 + '%').html(percentage.toFixed(0) + '%');
            cache.get("#wpstg-processing-status").html(percentage.toFixed(0) + '%' + ' - Step 2 of 4 Copying files...');
        }

        if (response.job === 'jobCopyDatabaseTmp' || response.job === 'jobSearchReplace' || response.job === 'jobData') {
            cache.get("#wpstg-progress-files").css('background-color', '#3bc36b');
            cache.get("#wpstg-progress-files").html('2. Files');
            cache.get("#wpstg-progress-files").width('30%');

            let percentage = 0;
            if (response.job === 'jobCopyDatabaseTmp') {
                percentage = response.percentage / 3;
            }
            else if (response.job === 'jobSearchReplace') {
                percentage = 100/3 + response.percentage / 3;
            }
            else {
                percentage = 200/3 + response.percentage / 3;
            }

            cache.get("#wpstg-progress-data").width(percentage * 0.4 + '%').html(percentage.toFixed(0) + '%');
            cache.get("#wpstg-processing-status").html(percentage.toFixed(0) + '%' + ' - Step 3 of 4 Copying data...');
        }
        if (response.job === 'jobDatabaseRename') {
            cache.get("#wpstg-progress-data").css('background-color', '#3bc36b');
            cache.get("#wpstg-progress-data").html('3. Data');
            cache.get("#wpstg-progress-data").width('40%');

            cache.get("#wpstg-progress-finishing").width(response.percentage * 0.15 + '%').html(response.percentage + '%');
            cache.get("#wpstg-processing-status").html(response.percentage.toFixed(0) + '%' + ' - Step 4 of 4 Finishing migration...');
        }
        if (response.status === "finished") {
            cache.get("#wpstg-progress-finishing").css('background-color', '#3bc36b');
            cache.get("#wpstg-progress-finishing").html('4. Finishing migration');
            cache.get("#wpstg-progress-finishing").width('15%');

            cache.get("#wpstg-processing-status").html(response.percentage.toFixed(0) + '%' + ' - Pushing Process Finished');
        }
    }

    that.init = function () {
        startProcess();
        startScanning();
        connectDatabase();
        editCloneData();
    }

    return that;

}(jQuery);


console.log(WPStaging);


jQuery(document).ready(function ($) {
    WPStagingPro.init();
});
