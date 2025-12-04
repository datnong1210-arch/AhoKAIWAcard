/**
 * Nihongo Flashcard - Frontend JavaScript
 *
 * Flashcard logic, keyboard navigation, touch/swipe support
 */

(function() {
    'use strict';

    /**
     * Flashcard Deck Class
     */
    class NihongoFlashcard {
        constructor(container) {
            this.container = container;
            this.deckId = parseInt(container.dataset.deckId, 10);
            this.cards = [];
            this.currentIndex = 0;
            this.isRevealed = false;
            this.isShuffled = false;
            this.originalOrder = [];
            this.learnedCards = new Set();

            // Touch/swipe tracking
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchEndX = 0;
            this.touchEndY = 0;

            this.init();
        }

        init() {
            // Parse cards from data attribute
            const cardsData = this.container.dataset.cards;
            if (cardsData) {
                try {
                    this.cards = JSON.parse(cardsData);
                    this.originalOrder = [...this.cards];
                } catch (e) {
                    console.error('Failed to parse cards data:', e);
                    return;
                }
            }

            // Get DOM elements
            this.cardElement = this.container.querySelector('.nihongo-flashcard');
            this.cardTop = this.container.querySelector('.nihongo-card-japanese');
            this.cardBottom = this.container.querySelector('.nihongo-card-bottom');
            this.cardMeaning = this.container.querySelector('.nihongo-card-meaning');
            this.cardAudioBtn = this.container.querySelector('.nihongo-card-audio-btn');
            this.progressFill = this.container.querySelector('.nihongo-progress-fill');
            this.progressText = this.container.querySelector('.nihongo-progress-count');
            this.flipHint = this.container.querySelector('.nihongo-flip-hint');
            this.learnedBtn = this.container.querySelector('.nihongo-mark-learned-btn');

            // Get control buttons
            this.prevBtn = this.container.querySelector('.nihongo-btn-prev');
            this.nextBtn = this.container.querySelector('.nihongo-btn-next');
            this.flipBtn = this.container.querySelector('.nihongo-btn-flip');
            this.shuffleBtn = this.container.querySelector('.nihongo-btn-shuffle');

            // Load progress from localStorage for guests or from server for users
            this.loadProgress();

            // Bind events
            this.bindEvents();

            // Show first card
            this.showCard(0);
        }

        bindEvents() {
            // Navigation buttons
            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', () => this.prevCard());
            }
            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', () => this.nextCard());
            }
            if (this.flipBtn) {
                this.flipBtn.addEventListener('click', () => this.toggleReveal());
            }
            if (this.shuffleBtn) {
                this.shuffleBtn.addEventListener('click', () => this.toggleShuffle());
            }

            // Card click to flip
            if (this.cardElement) {
                this.cardElement.addEventListener('click', () => this.toggleReveal());
            }

            // Mark learned button
            if (this.learnedBtn) {
                this.learnedBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleLearned();
                });
            }

            // Keyboard navigation
            document.addEventListener('keydown', (e) => this.handleKeyboard(e));

            // Touch/swipe support
            if (this.cardElement) {
                this.cardElement.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
                this.cardElement.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: true });
                this.cardElement.addEventListener('touchend', () => this.handleTouchEnd());
            }
        }

        showCard(index) {
            if (index < 0 || index >= this.cards.length) return;

            this.currentIndex = index;
            const card = this.cards[index];

            // Update card content
            if (this.cardTop) {
                this.cardTop.textContent = card.front_top || '';
            }
            if (this.cardMeaning) {
                this.cardMeaning.textContent = card.front_bottom || '';
            }

            // Update audio button
            if (this.cardAudioBtn) {
                if (card.audio_url) {
                    this.cardAudioBtn.style.display = 'flex';
                    this.cardAudioBtn.dataset.audioUrl = card.audio_url;
                    // Reinitialize audio button
                    if (window.CardAudioButton) {
                        new window.CardAudioButton(this.cardAudioBtn);
                    }
                } else {
                    this.cardAudioBtn.style.display = 'none';
                }
            }

            // Reset reveal state
            this.isRevealed = false;
            if (this.cardBottom) {
                this.cardBottom.classList.remove('visible');
            }

            // Update learned button state
            this.updateLearnedButton();

            // Update progress
            this.updateProgress();
        }

        nextCard() {
            if (this.currentIndex < this.cards.length - 1) {
                this.animateCard('slide-left');
                setTimeout(() => {
                    this.showCard(this.currentIndex + 1);
                }, 150);
            }
        }

        prevCard() {
            if (this.currentIndex > 0) {
                this.animateCard('slide-right');
                setTimeout(() => {
                    this.showCard(this.currentIndex - 1);
                }, 150);
            }
        }

        toggleReveal() {
            this.isRevealed = !this.isRevealed;

            if (this.cardBottom) {
                if (this.isRevealed) {
                    this.cardBottom.classList.add('visible');
                } else {
                    this.cardBottom.classList.remove('visible');
                }
            }
        }

        toggleShuffle() {
            if (this.isShuffled) {
                // Restore original order
                this.cards = [...this.originalOrder];
                this.isShuffled = false;
                if (this.shuffleBtn) {
                    this.shuffleBtn.classList.remove('active');
                }
            } else {
                // Shuffle cards
                this.cards = this.shuffleArray([...this.cards]);
                this.isShuffled = true;
                if (this.shuffleBtn) {
                    this.shuffleBtn.classList.add('active');
                }

                // Animate
                this.animateCard('shuffling');
            }

            // Reset to first card
            this.showCard(0);
            this.showToast(this.isShuffled ? nihongoFlashcard.i18n.shuffled : 'Đã khôi phục thứ tự');
        }

        shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        toggleLearned() {
            const cardId = this.currentIndex;

            if (this.learnedCards.has(cardId)) {
                this.learnedCards.delete(cardId);
            } else {
                this.learnedCards.add(cardId);
            }

            this.updateLearnedButton();
            this.saveProgress();
            this.updateProgress();
        }

        updateLearnedButton() {
            if (!this.learnedBtn) return;

            if (this.learnedCards.has(this.currentIndex)) {
                this.learnedBtn.classList.add('learned');
                this.learnedBtn.querySelector('span').textContent = 'Đã học';
            } else {
                this.learnedBtn.classList.remove('learned');
                this.learnedBtn.querySelector('span').textContent = 'Đánh dấu đã học';
            }
        }

        updateProgress() {
            const total = this.cards.length;
            const current = this.currentIndex + 1;
            const learned = this.learnedCards.size;

            // Update progress bar
            if (this.progressFill) {
                const percent = (current / total) * 100;
                this.progressFill.style.width = percent + '%';
            }

            // Update progress text
            if (this.progressText) {
                this.progressText.textContent = `${current} / ${total}`;
            }

            // Update learned count if element exists
            const learnedCount = this.container.querySelector('.nihongo-learned-count');
            if (learnedCount) {
                learnedCount.textContent = `${learned} đã học`;
            }
        }

        loadProgress() {
            // Try to load from localStorage first
            const storageKey = `nihongo_progress_${this.deckId}`;
            const savedProgress = localStorage.getItem(storageKey);

            if (savedProgress) {
                try {
                    const data = JSON.parse(savedProgress);
                    this.learnedCards = new Set(data.learned || []);
                } catch (e) {
                    console.error('Failed to load progress:', e);
                }
            }
        }

        saveProgress() {
            const storageKey = `nihongo_progress_${this.deckId}`;
            const data = {
                learned: Array.from(this.learnedCards),
                lastIndex: this.currentIndex,
                timestamp: Date.now()
            };

            localStorage.setItem(storageKey, JSON.stringify(data));

            // Also save to server if user is logged in
            if (typeof nihongoFlashcard !== 'undefined' && nihongoFlashcard.ajaxUrl) {
                fetch(nihongoFlashcard.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'nihongo_save_progress',
                        nonce: nihongoFlashcard.nonce,
                        deck_id: this.deckId,
                        card_index: this.currentIndex,
                        learned: this.learnedCards.has(this.currentIndex) ? 1 : 0
                    })
                }).catch(error => {
                    console.log('Progress save failed (guest mode):', error);
                });
            }
        }

        animateCard(animationClass) {
            if (!this.cardElement) return;

            this.cardElement.classList.add(animationClass);
            setTimeout(() => {
                this.cardElement.classList.remove(animationClass);
            }, 500);
        }

        handleKeyboard(e) {
            // Only handle if this deck is visible/focused
            if (!this.isVisible()) return;

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.prevCard();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.nextCard();
                    break;
                case ' ':
                    e.preventDefault();
                    this.toggleReveal();
                    break;
                case 's':
                case 'S':
                    e.preventDefault();
                    this.toggleShuffle();
                    break;
            }
        }

        handleTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
            this.touchStartY = e.changedTouches[0].screenY;
        }

        handleTouchMove(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            this.touchEndY = e.changedTouches[0].screenY;
        }

        handleTouchEnd() {
            const diffX = this.touchStartX - this.touchEndX;
            const diffY = this.touchStartY - this.touchEndY;

            // Minimum swipe distance
            const minSwipe = 50;

            // Check if horizontal swipe is dominant
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > minSwipe) {
                if (diffX > 0) {
                    // Swipe left - next card
                    this.nextCard();
                } else {
                    // Swipe right - prev card
                    this.prevCard();
                }
            } else if (Math.abs(diffY) > minSwipe && Math.abs(diffY) > Math.abs(diffX)) {
                // Vertical swipe - toggle reveal
                this.toggleReveal();
            }

            // Reset
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchEndX = 0;
            this.touchEndY = 0;
        }

        isVisible() {
            const rect = this.container.getBoundingClientRect();
            return rect.top < window.innerHeight && rect.bottom > 0;
        }

        showToast(message) {
            // Remove existing toast
            const existingToast = document.querySelector('.nihongo-toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Create toast
            const toast = document.createElement('div');
            toast.className = 'nihongo-toast';
            toast.textContent = message;
            document.body.appendChild(toast);

            // Show toast
            setTimeout(() => toast.classList.add('show'), 10);

            // Hide and remove toast
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }
    }

    /**
     * Course Grid Class
     */
    class NihongoCourse {
        constructor(container) {
            this.container = container;
            this.courseId = parseInt(container.dataset.courseId, 10);
            this.currentDeckId = null;

            this.init();
        }

        init() {
            // Get deck cards
            this.deckCards = this.container.querySelectorAll('.nihongo-deck-card');
            this.deckContainer = this.container.querySelector('.nihongo-deck-detail-container');

            // Bind events
            this.bindEvents();
        }

        bindEvents() {
            this.deckCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    const deckId = parseInt(card.dataset.deckId, 10);
                    this.loadDeck(deckId);
                });
            });

            // Back button
            const backBtn = this.container.querySelector('.nihongo-back-to-course');
            if (backBtn) {
                backBtn.addEventListener('click', () => this.showGrid());
            }
        }

        loadDeck(deckId) {
            if (!nihongoFlashcard || !nihongoFlashcard.ajaxUrl) return;

            this.currentDeckId = deckId;

            // Show loading
            if (this.deckContainer) {
                this.deckContainer.innerHTML = '<div class="nihongo-loading">' + nihongoFlashcard.i18n.loading + '</div>';
                this.deckContainer.style.display = 'block';
            }

            // Hide grid
            const grid = this.container.querySelector('.nihongo-deck-grid');
            if (grid) {
                grid.style.display = 'none';
            }

            // Fetch deck data via AJAX
            fetch(nihongoFlashcard.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'nihongo_get_deck_cards',
                    nonce: nihongoFlashcard.nonce,
                    deck_id: deckId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderDeck(deckId, data.data);
                } else {
                    this.showError(data.data.message || nihongoFlashcard.i18n.error);
                }
            })
            .catch(error => {
                console.error('Failed to load deck:', error);
                this.showError(nihongoFlashcard.i18n.error);
            });
        }

        renderDeck(deckId, data) {
            if (!this.deckContainer) return;

            // Find deck title from cards
            const deckCard = this.container.querySelector(`[data-deck-id="${deckId}"]`);
            const deckTitle = deckCard ? deckCard.querySelector('.nihongo-deck-name').textContent : '';

            // Build HTML for flashcard display
            const html = this.buildDeckHTML(deckId, deckTitle, data.cards, data.audio_url);
            this.deckContainer.innerHTML = html;

            // Initialize the flashcard
            const flashcardContainer = this.deckContainer.querySelector('.nihongo-flashcard-container');
            if (flashcardContainer) {
                new NihongoFlashcard(flashcardContainer);
            }

            // Initialize audio player if exists
            const audioPlayer = this.deckContainer.querySelector('.nihongo-audio-player');
            if (audioPlayer && window.NihongoAudioPlayer) {
                new window.NihongoAudioPlayer(audioPlayer);
            }
        }

        buildDeckHTML(deckId, title, cards, audioUrl) {
            // This would normally use a template, but for simplicity we'll build inline
            // In production, this should fetch the template from server
            return `
                <div class="nihongo-flashcard-container" data-deck-id="${deckId}" data-cards='${JSON.stringify(cards).replace(/'/g, "&#39;")}'>
                    <button type="button" class="nihongo-back-to-course button">&larr; Quay lại</button>
                    <h2 class="nihongo-deck-title">${this.escapeHtml(title)}</h2>

                    ${audioUrl ? this.buildAudioPlayerHTML(audioUrl) : ''}

                    <div class="nihongo-flashcard-wrapper">
                        <div class="nihongo-flashcard">
                            <div class="nihongo-card-top">
                                <div class="nihongo-card-japanese"></div>
                            </div>
                            <div class="nihongo-card-divider"></div>
                            <div class="nihongo-card-bottom">
                                <div class="nihongo-card-meaning"></div>
                            </div>
                            <span class="nihongo-flip-hint">Nhấn để lật thẻ</span>
                        </div>
                    </div>

                    <div class="nihongo-controls">
                        <button type="button" class="nihongo-btn nihongo-btn-prev" title="Thẻ trước">
                            <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                        </button>
                        <button type="button" class="nihongo-btn nihongo-btn-shuffle" title="Xáo trộn">
                            <svg viewBox="0 0 24 24"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg>
                        </button>
                        <button type="button" class="nihongo-btn nihongo-btn-primary nihongo-btn-flip" title="Lật thẻ">
                            <svg viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                        </button>
                        <button type="button" class="nihongo-btn nihongo-btn-next" title="Thẻ sau">
                            <svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
                        </button>
                    </div>

                    <div class="nihongo-progress-wrapper">
                        <div class="nihongo-progress-bar">
                            <div class="nihongo-progress-fill" style="width: 0%"></div>
                        </div>
                        <div class="nihongo-progress-text">
                            <span class="nihongo-progress-count">1 / ${cards.length}</span>
                            <span class="nihongo-learned-count">0 đã học</span>
                        </div>
                    </div>

                    <div class="nihongo-mark-learned">
                        <button type="button" class="nihongo-mark-learned-btn">
                            <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            <span>Đánh dấu đã học</span>
                        </button>
                    </div>
                </div>
            `;
        }

        buildAudioPlayerHTML(audioUrl) {
            return `
                <div class="nihongo-audio-player" data-audio-url="${this.escapeHtml(audioUrl)}">
                    <div class="nihongo-audio-player-inner">
                        <button type="button" class="nihongo-audio-play-btn">
                            <svg class="play-icon" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <svg class="pause-icon" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                        </button>
                        <div class="nihongo-audio-progress-section">
                            <div class="nihongo-audio-progress-wrapper">
                                <div class="nihongo-audio-progress-bar">
                                    <div class="nihongo-audio-progress-fill"></div>
                                </div>
                                <div class="nihongo-audio-progress-handle"></div>
                            </div>
                            <div class="nihongo-audio-time">
                                <span class="nihongo-audio-current">0:00</span>
                                <span class="nihongo-audio-duration">0:00</span>
                            </div>
                        </div>
                        <div class="nihongo-audio-volume">
                            <button type="button" class="nihongo-audio-volume-btn">
                                <svg class="volume-on" viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                                <svg class="volume-low" viewBox="0 0 24 24"><path d="M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z"/></svg>
                                <svg class="volume-muted" viewBox="0 0 24 24"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
                            </button>
                            <div class="nihongo-audio-volume-slider-wrapper">
                                <div class="nihongo-audio-volume-slider">
                                    <div class="nihongo-audio-volume-fill"></div>
                                    <div class="nihongo-audio-volume-handle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        showGrid() {
            if (this.deckContainer) {
                this.deckContainer.style.display = 'none';
                this.deckContainer.innerHTML = '';
            }

            const grid = this.container.querySelector('.nihongo-deck-grid');
            if (grid) {
                grid.style.display = 'grid';
            }

            this.currentDeckId = null;
        }

        showError(message) {
            if (this.deckContainer) {
                this.deckContainer.innerHTML = `<div class="nihongo-error">${this.escapeHtml(message)}</div>`;
            }
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize flashcard containers
        document.querySelectorAll('.nihongo-flashcard-container').forEach(container => {
            new NihongoFlashcard(container);
        });

        // Initialize course containers
        document.querySelectorAll('.nihongo-course-container').forEach(container => {
            new NihongoCourse(container);
        });
    });

    // Export for external use
    window.NihongoFlashcard = NihongoFlashcard;
    window.NihongoCourse = NihongoCourse;
})();
