/**
 * Administration script for the ImgSEO plugin
 */

// Global variables to track ongoing operations
var processingButtons = {};
var globalStopInProgress = false;
var jobDeletionInProgress = false;

/**
 * Function to handle button clicks preventing multiple clicks
 * @param {string} buttonSelector - The CSS selector for the button
 * @param {Function} handlerFunction - The function to execute on click
 */
function setupSafeButtonHandler(buttonSelector, handlerFunction) {
    jQuery(document).on('click', buttonSelector, function(e) {
        e.preventDefault();
        
        var $button = jQuery(this);
        var buttonId = $button.attr('id') || $button.data('button-id') || 'button-' + Math.random().toString(36).substring(2, 10);
        
        // If the button is already being processed, ignore the click
        if (processingButtons[buttonId]) {
            return;
        }
        
        // Mark the button as processing
        processingButtons[buttonId] = true;
        
        // Original button state
        var originalText = $button.text();
        var isDisabled = $button.prop('disabled');
        
        // Set processing state
        $button.prop('disabled', true);
        $button.text('Processing...');
        
        // Call the handler function with a callback to restore the state
        var callbackInvoked = false;
        
        var completeCallback = function() {
            if (callbackInvoked) return; // Prevent multiple calls
            callbackInvoked = true;
            
            // Safety timeout to ensure the button is reactivated
            // even if an error occurs in the handler function
            setTimeout(function() {
                // Restore the original button state
                $button.prop('disabled', isDisabled);
                $button.text(originalText);
                
                // Remove the button from the list of those being processed
                delete processingButtons[buttonId];
            }, 200);
        };
        
        try {
            // Pass the button, event and callback to the handler function
            handlerFunction.call(this, $button, e, completeCallback);
        } catch (error) {
            completeCallback();
        }
    });
}

function setupStopJobHandlers() {
    // Remove any previous handlers to avoid duplication
    jQuery(document).off('click', '.stop-job-button, #imgseo-stop');
    
    // New unified handler for all stop buttons (exclude bulk compression context)
    jQuery(document).on('click', '.stop-job-button, #imgseo-stop', function(e) {
        // Skip if this is in the bulk compression context
        if (jQuery(this).closest('#imgseo-bulk-compression-form').length > 0) {
            return; // Let the bulk compression handler manage this
        }

        e.preventDefault();

        // Check if there is already an interruption in progress at the global level
        if (globalStopInProgress) {
            return;
        }
        
        var $button = jQuery(this);
        
        // First read the direct HTML attribute, then the data-attribute
        var jobId = $button.attr('data-job-id') || $button.data('job-id');
        
        if (!jobId) {
            // A further attempt to retrieve the ID from the context
            if ($button.attr('id') && $button.attr('id').indexOf('stop-job-') === 0) {
                jobId = $button.attr('id').replace('stop-job-', '');
            } else {
                alert("Error: Unable to find the job ID to stop");
                return;
            }
        }
        
        // ONLY ONE confirm, with a clear message
        if (!confirm('Are you sure you want to stop this job? This operation cannot be undone.')) {
            return;
        }
        
        // Immediately set the global flag to prevent other calls
        globalStopInProgress = true;
        
        // Visually disable all stop buttons
        jQuery('.stop-job-button, #imgseo-stop').prop('disabled', true);
        $button.text('Stopping...');
        
        // Update UI to indicate interruption
        var $progressText = jQuery('#progress-text');
        if ($progressText.length) {
            $progressText.text("Stopping in progress, please wait...");
        }
        
        // Fix: More robust handling of AJAX URL and nonce
        var ajaxUrl = (typeof ImgSEO !== 'undefined' && ImgSEO.ajax_url) ? 
            ImgSEO.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            
        var securityNonce = (typeof ImgSEO !== 'undefined' && ImgSEO.nonce) ? 
            ImgSEO.nonce : '';
        
        // Set an attempt counter
        var stopAttempts = 0;
        var maxStopAttempts = 3;
        
        // Function to make the stop attempt
        function attemptStopJob() {
            stopAttempts++;
            
            // AJAX call
            // Trova il conteggio attuale delle immagini elaborate
            var currentProcessedCount = 0;
            var $progressText = jQuery('#progress-text');
            
            if ($progressText.length) {
                // Estrai il conteggio attuale dal testo di avanzamento se disponibile
                var progressText = $progressText.text();
                var match = progressText.match(/(\d+) of/);
                if (match && match[1]) {
                    currentProcessedCount = parseInt(match[1], 10);
                }
            }
            
            jQuery.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'imgseo_stop_job',
                    job_id: jobId,
                    processed_count: currentProcessedCount, // Passare il conteggio corrente
                    security: securityNonce
                },
                success: function(response) {
                    
                    // Even if there's an error in the response, we ignore the error message
                    // if the error indicates that the job is already stopped/completed
                    var isAlreadyStoppedError = response.data &&
                        response.data.message &&
                        response.data.message.indexOf('already completed or stopped') !== -1;
                    
                    if (response.success || isAlreadyStoppedError) {
                        // Imposta immediatamente il flag per fermare l'elaborazione
                        if (typeof window.isJobStopped !== 'undefined') {
                            window.isJobStopped = true;
                        }
                        
                        // Ferma anche eventuali timer attivi
                        if (typeof window.processingTimer !== 'undefined') {
                            clearInterval(window.processingTimer);
                        }
                        
                        if ($progressText.length) {
                            // Mostra il conteggio delle immagini elaborate nella risposta
                            var processedImages = response.data && response.data.processed_images ? response.data.processed_images : currentProcessedCount;
                            $progressText.text('Job stopped: ' + processedImages + ' images processed. Reloading page...');
                        }
                        
                        // Job fermato con successo, ricarica la pagina
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        // Se non è riuscito e non abbiamo raggiunto il numero massimo di tentativi, riprova
                        if (stopAttempts < maxStopAttempts) {
                            setTimeout(attemptStopJob, 1000); // Riprova dopo 1 secondo
                        } else {
                            // Solo in caso di errori gravi dopo tutti i tentativi
                            if (!isAlreadyStoppedError) {
                                alert('Could not stop the job after multiple attempts. The page will be reloaded.');
                            }
                            // Ricarica comunque la pagina dopo i tentativi
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    
                    // In caso di errore di rete, riprova se non abbiamo raggiunto il limite
                    if (stopAttempts < maxStopAttempts) {
                        setTimeout(attemptStopJob, 1000); // Riprova dopo 1 secondo
                    } else {
                        if ($progressText.length) {
                            $progressText.text('Connection error. Reloading page...');
                        }
                        
                        // Ricarica la pagina dopo aver esaurito i tentativi
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }
            });
        }
        
        // Avvia il primo tentativo
        attemptStopJob();
        
        // In any case, we reduce the number of interactions required
        // from the user by automatically reloading the page after a maximum timeout
        setTimeout(function() {
            if (globalStopInProgress) {
                location.reload();
            }
        }, 10000); // Maximum timeout of 10 seconds
    });
}

function setupDeleteJobHandlers() {
    // First remove any previous handlers to avoid duplication
    jQuery(document).off('click', '.delete-job-button');
    
    // Add a single handler
    jQuery(document).on('click', '.delete-job-button', function(e) {
        e.preventDefault();
        
        // If there is already a deletion in progress, ignore this click
        if (jobDeletionInProgress) {
            return;
        }

        var $button = jQuery(this);
        var jobId = $button.data('job-id');
        
        if (!jobId) {
            return;
        }
        
        // ONLY ONE confirm with a clear message
        if (!confirm('Are you sure you want to delete this job?')) {
            return;
        }
        
        // Set the flag to prevent multiple clicks
        jobDeletionInProgress = true;
        
        // Disable all delete buttons
        jQuery('.delete-job-button').prop('disabled', true);
        
        // Fix: More robust handling of AJAX URL and nonce
        var ajaxUrl = (typeof ImgSEO !== 'undefined' && ImgSEO.ajax_url) ?
            ImgSEO.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            
        var securityNonce = (typeof ImgSEO !== 'undefined' && ImgSEO.nonce) ?
            ImgSEO.nonce : '';
        
        // AJAX call to delete the job
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'imgseo_delete_job',
                job_id: jobId,
                security: securityNonce
            },
            success: function(response) {
                
                if (response.success) {
                    // No alert, instead we do an automatic reload
                    location.reload();
                } else {
                    // Only in case of error we show an alert
                    alert('Error while deleting the job: ' +
                         (response.data && response.data.message ? response.data.message : 'Unknown error'));
                    resetDeleteState();
                }
            },
            error: function(xhr, status, error) {
                alert('Connection error while deleting the job');
                resetDeleteState();
            }
        });
        
        function resetDeleteState() {
            jobDeletionInProgress = false;
            jQuery('.delete-job-button').prop('disabled', false);
        }
    });
    
    // Same approach for the "Delete all jobs" button
    jQuery(document).off('click', '#delete-all-jobs').on('click', '#delete-all-jobs', function(e) {
        e.preventDefault();
        
        if (jobDeletionInProgress) {
            return;
        }

        if (!confirm('Are you sure you want to delete all jobs?')) {
            return;
        }
        
        jobDeletionInProgress = true;
        jQuery('#delete-all-jobs, .delete-job-button').prop('disabled', true);
        
        var ajaxUrl = (typeof ImgSEO !== 'undefined' && ImgSEO.ajax_url) ? 
            ImgSEO.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
            
        var securityNonce = (typeof ImgSEO !== 'undefined' && ImgSEO.nonce) ? 
            ImgSEO.nonce : '';
        
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'imgseo_delete_all_jobs',
                security: securityNonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error while deleting jobs');
                    resetDeleteState();
                }
            },
            error: function() {
                alert('Connection error while deleting jobs');
                resetDeleteState();
            }
        });
        
        function resetDeleteState() {
            jobDeletionInProgress = false;
            jQuery('#delete-all-jobs, .delete-job-button').prop('disabled', false);
        }
    });
}

jQuery(document).ready(function($) {
    
    // Fix: robust handling of AJAX URL and nonce
    var ajaxUrl = (typeof ImgSEO !== 'undefined' && ImgSEO.ajax_url) ?
        ImgSEO.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
        
    var securityNonce = (typeof ImgSEO !== 'undefined' && ImgSEO.nonce) ?
        ImgSEO.nonce : '';
    
    // Initialize specific handlers for buttons
    setupStopJobHandlers();
    setupDeleteJobHandlers();
    
    // Setup per il pulsante generate-alt-text
    setupSafeButtonHandler('#generate-alt-text', function($button, e, completeCallback) {
        var attachmentId = $button.data('attachment-id');
        var $result = $('#alt-text-result');
        
        // Verify ID
        if (!attachmentId) {
            $result.addClass('error').text('Missing attachment ID').show();
            completeCallback(); // Important: always call the callback!
            return;
        }
        
        $result.removeClass('error success').empty();
        
        // AJAX call
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'imgseo_generate_alt_text',
                attachment_id: attachmentId,
                security: securityNonce
            },
            success: function(response) {
                
                if (response.success && response.data) {
                    // Update alt text field
                    var altText = response.data.alt_text;
                    var fields = ['#alt', '#attachment-details-two-column-alt-text', '#attachment_alt'];
                    
                    fields.forEach(function(selector) {
                        var $field = $(selector);
                        if ($field.length) {
                            $field.val(altText).trigger('change');
                        }
                    });
                    
                    // Also update other fields if present in the response
                    if (response.data.title) {
                        $('#title').val(response.data.title).trigger('change');
                    }
                    
                    if (response.data.caption) {
                        $('#excerpt').val(response.data.caption).trigger('change');
                    }
                    
                    if (response.data.description) {
                        $('#content').val(response.data.description).trigger('change');
                    }
                    
                    $result.addClass('success').text('Alt text updated successfully!').show();
                } else {
                    // Handle error
                    var errorMessage = response.data && response.data.message
                        ? response.data.message
                        : 'Error generating alt text';

                    // Check if it's an insufficient credits error
                    var isInsufficientCreditsError = errorMessage.indexOf('Insufficient') !== -1 ||
                                                    errorMessage.indexOf('insufficient') !== -1;

                    // Replace "Please purchase more credits to continue" with a link and add free credits info
                    if (isInsufficientCreditsError && errorMessage.indexOf('Please purchase more credits to continue') !== -1) {
                        errorMessage = errorMessage.replace(
                            'Please purchase more credits to continue.',
                            '<a href="https://dashboard.imgseo.net/subscription" target="_blank" style="color: #dc3232; text-decoration: underline; font-weight: 600;">Purchase more credits</a> or wait 24 hours to receive your 10 free daily credits.'
                        );
                    }

                    $result.addClass('error').html(errorMessage).show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $result.addClass('error').text('Error during generation: ' + errorThrown).show();
            },
            complete: function() {
                completeCallback();
            }
        });
    });
    
    // Setup per il pulsante bulk generate
    setupSafeButtonHandler('#imgseo-bulk-generate', function($button, e, completeCallback) {
        var overwrite = $('#bulk-generate-form input[name="overwrite"]').is(':checked') ? 1 : 0;
        var processingMode = 'async';
        
        // Get update options
        var updateTitle = $('#bulk-generate-form input[name="update_title"]').is(':checked') ? 1 : 0;
        var updateDescription = $('#bulk-generate-form input[name="update_description"]').is(':checked') ? 1 : 0;
        var updateCaption = $('#bulk-generate-form input[name="update_caption"]').is(':checked') ? 1 : 0;
        
        var $progressBarFill = $('#progress-bar-fill');
        var $progressText = $('#progress-text');
        var $progressContainer = $('#progress-container');
        var $progressDescription = $('#progress-description');

        // Clear any previous error messages
        $('#imgseo-notification-container').hide().empty();

        // IMMEDIATE FEEDBACK - Show progress container and spinner instantly
        $progressContainer.show();
        $progressBarFill.css('width', '0%');
        $progressText.html('<span class="imgseo-spinner"></span> Connecting to ImgSEO API...');
        $progressDescription.text("Processing will begin shortly. Keep this page open until completion.");
        
        // Add loading spinner CSS if not present
        if ($('.imgseo-spinner-style').length === 0) {
            $('head').append('<style class="imgseo-spinner-style">' +
                '.imgseo-spinner { display: inline-block; width: 16px; height: 16px; margin-right: 8px; ' +
                'border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; ' +
                'animation: imgseo-spin 1s linear infinite; } ' +
                '@keyframes imgseo-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
                '</style>');
        }
        
        
        // Update progress after a brief delay to show connection attempt
        setTimeout(function() {
            $progressText.html('<span class="imgseo-spinner"></span> Initializing bulk processing...');
        }, 500);
        
        // Start the process
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'imgseo_start_bulk',
                overwrite: overwrite,
                processing_mode: 'async',
                update_title: updateTitle,
                update_description: updateDescription,
                update_caption: updateCaption,
                processing_speed: $('#processing_speed').val() || 'normal', // Add the processing speed from the dropdown
                security: securityNonce
            },
            success: function(response) {

                if (response.success && response.data) {
                    var jobId = response.data.job_id;
                    var imageIds = response.data.image_ids;
                    var originalTotalImages = response.data.original_total_images || imageIds.length;
                    var creditLimited = response.data.credit_limited || false;

                    // Show successful connection and number of images found
                    $progressText.html('<span class="imgseo-spinner"></span> Processing ' + imageIds.length + ' images...');

                    // Show statistics box if images are limited by credits
                    if (creditLimited && originalTotalImages > imageIds.length) {
                        var statsMsg = '⚠️ Processing ' + imageIds.length + ' of ' + originalTotalImages + ' images selected (' + (originalTotalImages - imageIds.length) + ' images skipped due to insufficient credits)';
                        $('#stats-text').text(statsMsg);
                        $('#processing-statistics').show();
                    } else {
                        $('#processing-statistics').hide();
                    }

                    // Update the stop button with the job ID
                    $('#imgseo-stop').data('job-id', jobId);

                    // Start processing with a brief delay to show the count
                    setTimeout(function() {
                        var options = {
                            overwrite: overwrite,
                            update_title: updateTitle,
                            update_description: updateDescription,
                            update_caption: updateCaption,
                            original_total_images: originalTotalImages,
                            credit_limited: creditLimited
                        };
                        processAsyncBatch(jobId, imageIds, options);
                    }, 1000);
                    
                    // We don't call completeCallback() here because
                    // we want the button to remain disabled during processing
                } else {
                    $progressText.text(''); // Remove spinner
                    handleBulkError(response.data ? response.data.message : 'Error starting the process');
                    completeCallback(); // Riabilita il pulsante in caso di errore
                }
            },
            error: function(xhr, status, error) {
                $progressText.text(''); // Remove spinner
                
                var errorMsg = 'Connection error occurred';
                if (xhr.status === 500) {
                    errorMsg = 'Server error - please check your server configuration';
                } else if (xhr.status === 403) {
                    errorMsg = 'Permission denied - please refresh the page and try again';
                } else if (xhr.status === 0) {
                    errorMsg = 'Network connection failed - please check your internet connection';
                }
                
                handleBulkError(errorMsg + (error ? ': ' + error : ''));
                completeCallback(); // Riabilita il pulsante in caso di errore
            }
        });
    });
    // Function to process batch asynchronously
    // Funzione per elaborare batch in modo asincrono
    function processAsyncBatch(jobId, imageIds, options) {
        // Default options if not provided
        options = options || {};

        // Deduplica gli ID immagine per evitare duplicazioni nel batch
        var originalCount = imageIds.length;
        var uniqueImageIds = Array.from(new Set(imageIds));
        imageIds = uniqueImageIds;
    
        // Reset/inizializza gli stati per il batch corrente
        var processingImages = {};
        var loggedErrors = {};

        // Salva il conteggio totale come attributo dati sul testo di avanzamento per riferimento quando si interrompe
        $('#progress-text').data('total-images', imageIds.length);

        // Salva i dati di limitazione crediti
        var originalTotalImages = options.original_total_images || imageIds.length;
        var creditLimited = options.credit_limited || false;
        $('#progress-text').data('original-total-images', originalTotalImages);
        $('#progress-text').data('credit-limited', creditLimited);
    
        // Get processing speed from dropdown
        var processingSpeed = $('#processing_speed').val() || 'normal';
    
        // Configurazione per elaborazione parallela con matematica corretta
        // Logica: se API impiega 4 secondi e ho N richieste parallele,
        // uno slot si libera ogni 4/N secondi
        var processingDelay;
        var maxConcurrentRequests;
        var apiResponseTime = 4000; // 4 secondi in millisecondi
    
        switch(processingSpeed) {
            case 'slow':
                maxConcurrentRequests = 4;
                processingDelay = 1000; // 1s intervals
                break;
            case 'normal':
                maxConcurrentRequests = 6;
                processingDelay = 700; // 0.7s intervals
                break;
            case 'fast':
                maxConcurrentRequests = 8;
                processingDelay = 500; // 0.5s intervals
                break;
            case 'ultra':
                maxConcurrentRequests = 12;
                processingDelay = 400; // 0.4s intervals
                break;
            case 'insane':
                maxConcurrentRequests = 16;
                processingDelay = 200; // 0.2s intervals
                break;
            default:
                maxConcurrentRequests = 6;
                processingDelay = 700;
        }
        var completedImages = 0;
        var queue = [...imageIds];
        var $progressBarFill = $('#progress-bar-fill');
        var $progressText = $('#progress-text');
        var $logsContainer = $('#processing-logs');
        var isJobStopped = false;
        var currentImageIndex = 0;
        var activeRequests = 0; // Track concurrent requests
        
        // Aggiungi container per i log se non esiste
        if ($('#processing-logs-container').length === 0) {
            $('#progress-container').after('<div id="processing-logs-container" class="log-container"><h3>Real-time Processing Log</h3><div id="processing-logs" class="log-entries"></div></div>');
            $logsContainer = $('#processing-logs');
        } else {
            $logsContainer.empty();
        }
        
        // Funzione principale per elaborazione parallela
        function startParallelProcessing() {
            
            // Avvia le prime richieste fino al limite
            for (var i = 0; i < Math.min(maxConcurrentRequests, queue.length); i++) {
                setTimeout(function() {
                    processNextImage();
                }, i * processingDelay);
            }
        }
        
        function processNextImage() {
            // Controlla se il job è stato fermato
            if (isJobStopped) {
                return;
            }
            
            // Controlla se abbiamo finito tutte le immagini
            if (currentImageIndex >= queue.length) {
                // Non chiamiamo completeProcessing() qui perché potrebbero esserci ancora richieste attive
                return;
            }
            
            var imageId = queue[currentImageIndex];
            currentImageIndex++;
            
            // Verifica se l'immagine è già stata elaborata (doppio controllo)
            if (processingImages[imageId]) {
                // Continua immediatamente con la prossima immagine
                setTimeout(processNextImage, processingDelay);
                return;
            }
            
            // Marca l'immagine come in elaborazione
            processingImages[imageId] = true;
            activeRequests++;
            
            // Elabora l'immagine
            processImageParallel(imageId);
        }
        
        // Funzione per elaborare una singola immagine in parallelo
        function processImageParallel(imageId) {
            // Direttamente al processamento per maggiore velocità
            generateAltTextForImage(imageId);
        }
        
        // Funzione per gestire immagini non trovate
        function handleImageNotFound(imageId) {
            completedImages++;
            
            var logEntry = '<div class="log-entry log-warning">' +
                          '<span class="log-time">' + getCurrentTime() + '</span>' +
                          '<span class="log-filename">Image ' + imageId + '</span>' +
                          '<span class="log-text">Image no longer exists, skipped</span>' +
                          '</div>';
            
            $logsContainer.append(logEntry);
            $logsContainer.scrollTop($logsContainer[0].scrollHeight);
            
            updateProgress();
            scheduleNextImage();
        }
        
        // Funzione per generare alt text per un'immagine
        function generateAltTextForImage(imageId) {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'imgseo_generate_alt_text',
                    image_id: imageId,
                    job_id: jobId,
                    security: securityNonce,
                    overwrite: options.overwrite,
                    update_title: options.update_title,
                    update_description: options.update_description,
                    update_caption: options.update_caption
                },
                success: function(response) {
                    handleImageProcessingResult(imageId, response);
                },
                error: function(xhr, status, error) {
                    handleImageProcessingError(imageId, xhr, status, error);
                }
            });
        }
        
        // Funzione per gestire il risultato dell'elaborazione
        function handleImageProcessingResult(imageId, response) {
            activeRequests--; // Decrementa le richieste attive
            completedImages++;
            
            if (response.success) {
                var altText = response.data.alt_text;
                var filename = response.data.filename || 'Image ' + imageId;

                var logEntry = '<div class="log-entry log-success">' +
                              '<span class="log-time">' + getCurrentTime() + '</span>' +
                              '<span class="log-filename">' + (filename + ' (#' + imageId + ')') + '</span>' +
                              '<span class="log-text" title="' + altText.replace(/"/g, '&quot;') + '">' + 
                              altText + '</span>' +
                              '</div>';

                $logsContainer.append(logEntry);
                $logsContainer.scrollTop($logsContainer[0].scrollHeight);
            } else {
                handleImageError(imageId, response);
            }
            
            updateProgress();
            
            // Avvia la prossima immagine se ce ne sono ancora
            if (currentImageIndex < queue.length) {
                setTimeout(processNextImage, processingDelay);
            } else if (activeRequests === 0) {
                // Tutte le immagini sono state elaborate e non ci sono richieste attive
                completeProcessing();
            }
        }
        
        // Funzione per gestire errori di elaborazione
        function handleImageProcessingError(imageId, xhr, status, error) {
            activeRequests--; // Decrementa le richieste attive
            completedImages++;
            
            var statusCode = (xhr && typeof xhr.status !== 'undefined') ? xhr.status : null;
            var fallbackMsg = error || 'Unknown error';
            var errorMessage = statusCode ? ('API request failed with code: ' + statusCode + (fallbackMsg ? ' - ' + fallbackMsg : '')) : fallbackMsg;
            
            if (!loggedErrors[imageId]) {
                var logEntry = '<div class="log-entry log-error">' +
                              '<span class="log-time">' + getCurrentTime() + '</span>' +
                              '<span class="log-filename">Image #' + imageId + '</span>' +
                              '<span class="log-text">' + errorMessage + '</span>' +
                              '</div>';
                
                $logsContainer.append(logEntry);
                $logsContainer.scrollTop($logsContainer[0].scrollHeight);
                loggedErrors[imageId] = true;
            }
            
            updateProgress();
            
            // Avvia la prossima immagine se ce ne sono ancora
            if (currentImageIndex < queue.length) {
                setTimeout(processNextImage, processingDelay);
            } else if (activeRequests === 0) {
                // Tutte le immagini sono state elaborate e non ci sono richieste attive
                completeProcessing();
            }
        }
    
        // Funzione per gestire errori specifici dell'immagine
        function handleImageError(imageId, response) {
            var errorMessage = response.data ? response.data.message : 'Unknown error';
            var isInsufficientCredits = 
                errorMessage.indexOf('Insufficient') !== -1 || 
                errorMessage.indexOf('insufficient') !== -1 ||
                errorMessage.indexOf('crediti insufficienti') !== -1 ||
                (response.data && response.data.error_type === 'insufficient_credits');
            
            if (isInsufficientCredits) {
                isJobStopped = true;

                $progressText.text('Processing stopped: Insufficient credits. ' +
                                  completedImages + ' of ' + queue.length + ' images processed.');
                
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'imgseo_stop_job',
                        job_id: jobId,
                        security: securityNonce
                    },
                    success: function() {
                        setTimeout(function() {
                            $('#imgseo-bulk-generate').prop('disabled', false);
                        }, 2000);
                    }
                });
                return;
            }
            
            // Log dell'errore (evita duplicazioni)
            if (!loggedErrors[imageId]) {
                var filename = response.data ? response.data.filename || 'Image ' + imageId : 'Image ' + imageId;
                var logEntry = '<div class="log-entry log-error">' +
                              '<span class="log-time">' + getCurrentTime() + '</span>' +
                              '<span class="log-filename">' + (filename + ' (#' + imageId + ')') + '</span>' +
                              '<span class="log-text">' + errorMessage + '</span>' +
                              '</div>';
                
                $logsContainer.append(logEntry);
                $logsContainer.scrollTop($logsContainer[0].scrollHeight);
                loggedErrors[imageId] = true;
            }
        }
        
        // Funzione per aggiornare il progresso
        function updateProgress() {
            var progress = Math.round((completedImages / queue.length) * 100);
            $progressBarFill.css('width', progress + '%');

            var remainingImages = queue.length - completedImages;
            var progressMsg = 'Processing: ' + completedImages + ' of ' + queue.length + ' images completed (' + progress + '%)';

            if (activeRequests > 0) {
                progressMsg += ' - ' + activeRequests + ' requests in progress';
            }

            if (remainingImages > 0 && activeRequests > 0) {
                // Stima basata sul processamento parallelo
                var avgTimePerImage = 4; // seconds (API response time)
                var estimatedTimeMinutes = Math.ceil((remainingImages * avgTimePerImage) / (maxConcurrentRequests * 60));
                if (estimatedTimeMinutes > 1) {
                    progressMsg += ' - Est. time: ~' + estimatedTimeMinutes + ' min';
                } else {
                    progressMsg += ' - Almost done!';
                }
            }

            $progressText.text(progressMsg);
            $('#progress-text').data('completed-images', completedImages);
        }
        
        // Funzione rimossa - ora la logica è gestita in handleImageProcessingResult e handleImageProcessingError
        
        // Funzione per completare l'elaborazione
        function completeProcessing() {
            $('#imgseo-bulk-generate').prop('disabled', false);
            $progressText.text('Processing completed: ' + completedImages + ' images processed');
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'imgseo_stop_job',
                    job_id: jobId,
                    processed_count: completedImages,
                    completion_status: 'completed',
                    security: securityNonce
                },
                success: function(response) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                },
                error: function(xhr, status, error) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            });
        }
        
        // Funzione per ottenere l'ora corrente formattata
        function getCurrentTime() {
            var now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + 
                   now.getMinutes().toString().padStart(2, '0') + ':' + 
                   now.getSeconds().toString().padStart(2, '0');
        }
        
        // Avvia l'elaborazione parallela
        startParallelProcessing();
    }
    
    
    // Funzione per errori bulk
    function handleBulkError(errorMessage) {
        $('#progress-container').hide();
        $('#processing-logs-container').hide();
        $('#imgseo-bulk-generate').prop('disabled', false);

        // Check if it's an insufficient credits error
        var isInsufficientCreditsError = errorMessage.indexOf('Insufficient') !== -1 ||
                                        errorMessage.indexOf('insufficient') !== -1;

        // Replace "Please purchase more credits to continue" with a link and add free credits info
        if (isInsufficientCreditsError && errorMessage.indexOf('Please purchase more credits to continue') !== -1) {
            errorMessage = errorMessage.replace(
                'Please purchase more credits to continue.',
                '<a href="https://dashboard.imgseo.net/subscription" target="_blank" style="color: #dc3232; text-decoration: underline; font-weight: 600;">Purchase more credits</a> or wait 24 hours to receive your 10 free daily credits.'
            );
        }

        // Show error inline instead of alert
        var $errorContainer = $('#imgseo-notification-container');
        $errorContainer.html(
            '<div style="background: #fff; border-left: 4px solid #dc3232; padding: 12px 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">' +
            '<p style="margin: 0; color: #dc3232; font-weight: 500;">⚠️ ' + errorMessage + '</p>' +
            '</div>'
        );
        $errorContainer.show();

        // Scroll to the error message
        $('html, body').animate({
            scrollTop: $errorContainer.offset().top - 100
        }, 500);
    }
    
    // Formatta il tempo
    function formatTime(datetime) {
        if (!datetime) return '';
        
        try {
            var date = new Date(datetime.replace(' ', 'T'));
            var hours = date.getHours().toString().padStart(2, '0');
            var minutes = date.getMinutes().toString().padStart(2, '0');
            var seconds = date.getSeconds().toString().padStart(2, '0');
            return hours + ':' + minutes + ':' + seconds;
        } catch (e) {
            return '';
        }
    }
    
    // Tronca il testo se più lungo di maxLength
    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength - 3) + '...' : text;
    }
    
    // Aggiungi un gestore per il pulsante "Forza elaborazione"
    $(document).on('click', '#force-cron-button', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $spinner = $('#cron-spinner');
        
        // Disabilita il pulsante e mostra lo spinner
        $button.prop('disabled', true);
        $spinner.css('visibility', 'visible');
        
        // Chiamata AJAX per forzare l'esecuzione del cron
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'imgseo_force_cron',
                security: securityNonce
            },
            success: function(response) {
                if (response.success) {
                    $('#cron-status-text').text('Status: Processing started manually');
                    $('#last-cron-run').text('Last update: ' + response.data.last_run + ' (' + response.data.time_ago + ')');
                    
                    // Show success message
                    alert('Processing started successfully! The process should begin shortly.');
                    
                    // Force a job status check after 3 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    $('#cron-status-text').html('<span style="color: red;">Status: Error starting the process</span>');
                    alert('An error occurred while starting the process.');
                }
            },
            error: function() {
                $('#cron-status-text').html('<span style="color: red;">Status: Connection error</span>');
                alert('A connection error occurred.');
            },
            complete: function() {
                // Riabilita il pulsante e nascondi lo spinner
                $button.prop('disabled', false);
                $spinner.css('visibility', 'hidden');
            }
        });
    });
    
    // Pulsante nascondi monitoraggio
    $(document).on('click', '#imgseo-cancel', function() {
        $('#progress-container').slideUp();
    });
});
