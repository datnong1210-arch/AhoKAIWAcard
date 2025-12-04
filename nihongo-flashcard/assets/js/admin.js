/**
 * Nihongo Flashcard - Admin JavaScript
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(function() {

        /**
         * Card Management
         */
        const CardManager = {
            init: function() {
                this.cardsList = $('#nihongo-cards-list');
                this.cardTemplate = $('#nihongo-card-template').html();
                this.cardCount = this.cardsList.find('.nihongo-card-row').length;

                this.bindEvents();
                this.initSortable();
            },

            bindEvents: function() {
                // Add new card
                $('.nihongo-add-card').on('click', () => this.addCard());

                // Delete card
                this.cardsList.on('click', '.nihongo-delete-card', function(e) {
                    e.preventDefault();
                    if (confirm(nihongoFlashcardAdmin.i18n.confirmDelete)) {
                        $(this).closest('.nihongo-card-row').fadeOut(300, function() {
                            $(this).remove();
                            CardManager.updateCardNumbers();
                            CardManager.updateCardCount();
                        });
                    }
                });
            },

            initSortable: function() {
                if ($.fn.sortable) {
                    this.cardsList.sortable({
                        handle: '.nihongo-card-handle',
                        placeholder: 'nihongo-card-row ui-sortable-placeholder',
                        update: () => this.updateCardNumbers()
                    });
                }
            },

            addCard: function() {
                const newCard = this.cardTemplate.replace(/\{\{INDEX\}\}/g, this.cardCount);
                this.cardsList.append(newCard);

                // Update card number
                const newRow = this.cardsList.find('.nihongo-card-row').last();
                newRow.find('.nihongo-card-number').text(this.cardCount + 1);

                // Animate
                newRow.hide().fadeIn(300);

                // Focus on first field
                newRow.find('textarea').first().focus();

                this.cardCount++;
                this.updateCardCount();
            },

            updateCardNumbers: function() {
                this.cardsList.find('.nihongo-card-row').each(function(index) {
                    $(this).find('.nihongo-card-number').text(index + 1);
                    // Update field names with new index
                    $(this).find('[name]').each(function() {
                        const name = $(this).attr('name');
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    });
                });
            },

            updateCardCount: function() {
                const count = this.cardsList.find('.nihongo-card-row').length;
                $('.nihongo-card-count').text('Tổng: ' + count + ' thẻ');
            }
        };

        /**
         * Course Deck Management
         */
        const CourseDeckManager = {
            init: function() {
                this.availableList = $('#nihongo-available-decks');
                this.selectedList = $('#nihongo-selected-decks');

                if (!this.availableList.length) return;

                this.bindEvents();
                this.initSortable();
            },

            bindEvents: function() {
                // Add deck to course
                this.availableList.on('click', '.nihongo-add-deck-to-course', function(e) {
                    e.preventDefault();
                    const item = $(this).closest('.nihongo-deck-item');
                    CourseDeckManager.addDeck(item);
                });

                // Remove deck from course
                this.selectedList.on('click', '.nihongo-remove-deck-from-course', function(e) {
                    e.preventDefault();
                    const item = $(this).closest('.nihongo-deck-item');
                    CourseDeckManager.removeDeck(item);
                });

                // Search decks
                $('#nihongo-deck-search').on('input', function() {
                    const query = $(this).val().toLowerCase();
                    CourseDeckManager.availableList.find('.nihongo-deck-item').each(function() {
                        const title = $(this).find('.nihongo-deck-title').text().toLowerCase();
                        $(this).toggle(title.includes(query));
                    });
                });
            },

            initSortable: function() {
                if ($.fn.sortable) {
                    this.selectedList.sortable({
                        handle: '.nihongo-deck-handle',
                        placeholder: 'nihongo-deck-item ui-sortable-placeholder'
                    });
                }
            },

            addDeck: function(item) {
                const deckId = item.data('id');
                const clone = item.clone();

                // Add handle and hidden input
                clone.prepend('<span class="nihongo-deck-handle dashicons dashicons-menu"></span>');
                clone.append('<input type="hidden" name="nihongo_course_decks[]" value="' + deckId + '">');

                // Change button
                clone.find('.nihongo-add-deck-to-course')
                    .removeClass('nihongo-add-deck-to-course')
                    .addClass('nihongo-remove-deck-from-course')
                    .find('.dashicons')
                    .removeClass('dashicons-plus')
                    .addClass('dashicons-minus');

                // Move to selected list
                this.selectedList.append(clone);
                item.fadeOut(200);

                // Remove empty message
                $('.nihongo-no-decks-selected').remove();
            },

            removeDeck: function(item) {
                const deckId = item.data('id');

                // Move back to available list
                const original = item.clone();
                original.find('.nihongo-deck-handle').remove();
                original.find('input[type="hidden"]').remove();

                original.find('.nihongo-remove-deck-from-course')
                    .removeClass('nihongo-remove-deck-from-course')
                    .addClass('nihongo-add-deck-to-course')
                    .find('.dashicons')
                    .removeClass('dashicons-minus')
                    .addClass('dashicons-plus');

                // Find position in available list or append
                const existingItem = this.availableList.find('[data-id="' + deckId + '"]');
                if (existingItem.length) {
                    existingItem.fadeIn(200);
                } else {
                    this.availableList.append(original.show());
                }

                item.fadeOut(200, function() {
                    $(this).remove();
                });
            }
        };

        /**
         * Audio Upload Handler
         */
        const AudioUploader = {
            init: function() {
                $('.nihongo-upload-audio').on('click', function(e) {
                    e.preventDefault();
                    const target = $($(this).data('target'));
                    AudioUploader.openMediaUploader(target);
                });

                $('.nihongo-preview-audio').on('click', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    AudioUploader.previewAudio(url);
                });
            },

            openMediaUploader: function(targetInput) {
                const frame = wp.media({
                    title: 'Chọn file audio',
                    button: { text: 'Chọn audio' },
                    library: { type: 'audio' },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    targetInput.val(attachment.url);
                });

                frame.open();
            },

            previewAudio: function(url) {
                // Stop any currently playing audio
                if (this.currentAudio) {
                    this.currentAudio.pause();
                }

                this.currentAudio = new Audio(url);
                this.currentAudio.play();
            },

            currentAudio: null
        };

        /**
         * Shortcode Copy Handler
         */
        const ShortcodeCopy = {
            init: function() {
                $('.nihongo-copy-shortcode').on('click', function(e) {
                    e.preventDefault();
                    const shortcode = $(this).data('shortcode');
                    ShortcodeCopy.copyToClipboard(shortcode, $(this));
                });
            },

            copyToClipboard: function(text, button) {
                navigator.clipboard.writeText(text).then(function() {
                    const originalText = button.text();
                    button.text('Đã sao chép!');
                    setTimeout(function() {
                        button.html('<span class="dashicons dashicons-clipboard"></span> Sao chép');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    button.text('Đã sao chép!');
                    setTimeout(function() {
                        button.html('<span class="dashicons dashicons-clipboard"></span> Sao chép');
                    }, 2000);
                });
            }
        };

        // Initialize all modules
        CardManager.init();
        CourseDeckManager.init();
        AudioUploader.init();
        ShortcodeCopy.init();

    });
})(jQuery);
