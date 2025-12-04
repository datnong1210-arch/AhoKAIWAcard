/**
 * Nihongo Flashcard - Audio Player
 *
 * Custom audio player with progress bar, volume control
 */

(function() {
    'use strict';

    /**
     * Audio Player Class
     */
    class NihongoAudioPlayer {
        constructor(container) {
            this.container = container;
            this.audio = null;
            this.isPlaying = false;
            this.isDragging = false;
            this.isVolumeDragging = false;

            this.init();
        }

        init() {
            const audioUrl = this.container.dataset.audioUrl;
            if (!audioUrl) return;

            // Create audio element
            this.audio = new Audio(audioUrl);
            this.audio.preload = 'metadata';

            // Get DOM elements
            this.playBtn = this.container.querySelector('.nihongo-audio-play-btn');
            this.progressWrapper = this.container.querySelector('.nihongo-audio-progress-wrapper');
            this.progressFill = this.container.querySelector('.nihongo-audio-progress-fill');
            this.progressHandle = this.container.querySelector('.nihongo-audio-progress-handle');
            this.currentTimeEl = this.container.querySelector('.nihongo-audio-current');
            this.durationEl = this.container.querySelector('.nihongo-audio-duration');
            this.volumeBtn = this.container.querySelector('.nihongo-audio-volume-btn');
            this.volumeSlider = this.container.querySelector('.nihongo-audio-volume-slider');
            this.volumeFill = this.container.querySelector('.nihongo-audio-volume-fill');
            this.volumeHandle = this.container.querySelector('.nihongo-audio-volume-handle');

            this.bindEvents();
        }

        bindEvents() {
            // Play/Pause
            if (this.playBtn) {
                this.playBtn.addEventListener('click', () => this.togglePlay());
            }

            // Audio events
            this.audio.addEventListener('loadedmetadata', () => this.onLoadedMetadata());
            this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
            this.audio.addEventListener('ended', () => this.onEnded());
            this.audio.addEventListener('error', () => this.onError());
            this.audio.addEventListener('waiting', () => this.container.classList.add('loading'));
            this.audio.addEventListener('canplay', () => this.container.classList.remove('loading'));

            // Progress bar drag
            if (this.progressWrapper) {
                this.progressWrapper.addEventListener('mousedown', (e) => this.startProgressDrag(e));
                this.progressWrapper.addEventListener('click', (e) => this.seekTo(e));
            }

            // Volume control
            if (this.volumeBtn) {
                this.volumeBtn.addEventListener('click', () => this.toggleMute());
            }

            if (this.volumeSlider) {
                this.volumeSlider.addEventListener('mousedown', (e) => this.startVolumeDrag(e));
                this.volumeSlider.addEventListener('click', (e) => this.setVolumeFromClick(e));
            }

            // Global mouse events for dragging
            document.addEventListener('mousemove', (e) => this.onMouseMove(e));
            document.addEventListener('mouseup', () => this.onMouseUp());

            // Touch events
            if (this.progressWrapper) {
                this.progressWrapper.addEventListener('touchstart', (e) => this.startProgressDrag(e), { passive: false });
                this.progressWrapper.addEventListener('touchmove', (e) => this.onTouchMove(e), { passive: false });
                this.progressWrapper.addEventListener('touchend', () => this.onMouseUp());
            }
        }

        togglePlay() {
            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        }

        play() {
            this.audio.play().then(() => {
                this.isPlaying = true;
                this.container.classList.add('playing');
            }).catch((error) => {
                console.error('Audio play error:', error);
            });
        }

        pause() {
            this.audio.pause();
            this.isPlaying = false;
            this.container.classList.remove('playing');
        }

        onLoadedMetadata() {
            if (this.durationEl) {
                this.durationEl.textContent = this.formatTime(this.audio.duration);
            }
        }

        onTimeUpdate() {
            const progress = (this.audio.currentTime / this.audio.duration) * 100;

            if (this.progressFill && !this.isDragging) {
                this.progressFill.style.width = progress + '%';
            }

            if (this.progressHandle && !this.isDragging) {
                this.progressHandle.style.left = progress + '%';
            }

            if (this.currentTimeEl) {
                this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
            }
        }

        onEnded() {
            this.isPlaying = false;
            this.container.classList.remove('playing');
            this.audio.currentTime = 0;
            this.updateProgress(0);
        }

        onError() {
            this.container.classList.add('error');
            this.container.classList.remove('loading', 'playing');
        }

        startProgressDrag(e) {
            e.preventDefault();
            this.isDragging = true;
            this.container.classList.add('dragging');
            this.seekTo(e);
        }

        seekTo(e) {
            const rect = this.progressWrapper.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            let percent = (clientX - rect.left) / rect.width;
            percent = Math.max(0, Math.min(1, percent));

            this.updateProgress(percent * 100);

            if (!this.isDragging) {
                this.audio.currentTime = percent * this.audio.duration;
            }
        }

        updateProgress(percent) {
            if (this.progressFill) {
                this.progressFill.style.width = percent + '%';
            }
            if (this.progressHandle) {
                this.progressHandle.style.left = percent + '%';
            }
        }

        onMouseMove(e) {
            if (this.isDragging) {
                this.seekTo(e);
            }
            if (this.isVolumeDragging) {
                this.setVolumeFromClick(e);
            }
        }

        onTouchMove(e) {
            if (this.isDragging) {
                e.preventDefault();
                this.seekTo(e);
            }
        }

        onMouseUp() {
            if (this.isDragging) {
                this.isDragging = false;
                this.container.classList.remove('dragging');

                const percent = parseFloat(this.progressFill.style.width) / 100;
                this.audio.currentTime = percent * this.audio.duration;
            }
            if (this.isVolumeDragging) {
                this.isVolumeDragging = false;
            }
        }

        toggleMute() {
            this.audio.muted = !this.audio.muted;

            if (this.audio.muted) {
                this.container.classList.add('muted');
            } else {
                this.container.classList.remove('muted');
            }
        }

        startVolumeDrag(e) {
            e.preventDefault();
            this.isVolumeDragging = true;
            this.setVolumeFromClick(e);
        }

        setVolumeFromClick(e) {
            if (!this.volumeSlider) return;

            const rect = this.volumeSlider.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            let percent = (clientX - rect.left) / rect.width;
            percent = Math.max(0, Math.min(1, percent));

            this.setVolume(percent);
        }

        setVolume(percent) {
            this.audio.volume = percent;

            if (this.volumeFill) {
                this.volumeFill.style.width = (percent * 100) + '%';
            }
            if (this.volumeHandle) {
                this.volumeHandle.style.left = (percent * 100) + '%';
            }

            // Update volume icon state
            this.container.classList.remove('muted', 'low-volume');
            if (percent === 0) {
                this.container.classList.add('muted');
            } else if (percent < 0.5) {
                this.container.classList.add('low-volume');
            }
        }

        formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';

            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    }

    /**
     * Card Audio Button
     */
    class CardAudioButton {
        constructor(button) {
            this.button = button;
            this.audio = null;
            this.isPlaying = false;

            this.init();
        }

        init() {
            const audioUrl = this.button.dataset.audioUrl;
            if (!audioUrl) return;

            this.audio = new Audio(audioUrl);

            this.button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });

            this.audio.addEventListener('ended', () => {
                this.isPlaying = false;
                this.button.classList.remove('playing');
            });
        }

        toggle() {
            if (this.isPlaying) {
                this.audio.pause();
                this.audio.currentTime = 0;
                this.isPlaying = false;
                this.button.classList.remove('playing');
            } else {
                // Stop any other playing card audio
                document.querySelectorAll('.nihongo-card-audio-btn.playing').forEach(btn => {
                    btn.click();
                });

                this.audio.play();
                this.isPlaying = true;
                this.button.classList.add('playing');
            }
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize main audio players
        document.querySelectorAll('.nihongo-audio-player').forEach(container => {
            new NihongoAudioPlayer(container);
        });

        // Initialize card audio buttons
        document.querySelectorAll('.nihongo-card-audio-btn').forEach(button => {
            new CardAudioButton(button);
        });
    });

    // Export for external use
    window.NihongoAudioPlayer = NihongoAudioPlayer;
    window.CardAudioButton = CardAudioButton;
})();
