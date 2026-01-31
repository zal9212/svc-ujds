
// Image Preview Modal Functions
window.openChatImage = function (src, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const modal = document.getElementById('image-preview-modal');
    const modalImg = document.getElementById('modal-image');
    if (modal && modalImg) {
        modalImg.src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        console.log("Image Preview Opened:", src);
    }
    return false;
};

window.closeChatImage = function () {
    const modal = document.getElementById('image-preview-modal');
    const modalImg = document.getElementById('modal-image');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (modalImg) modalImg.src = '';
        document.body.style.overflow = '';
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // Image Modal Events
    const modal = document.getElementById('image-preview-modal');
    if (modal) {
        modal.onclick = function (e) {
            if (e.target === modal) window.closeChatImage();
        };
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closeChatImage();
    });

    // 1. Scroll to bottom
    const chatContainer = document.getElementById('chat-messages');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

    // 2. Vocal Players Initialization
    initVocalPlayers();

    // 3. Media Auto-submit
    const mediaInput = document.querySelector('input[name="image"]');
    if (mediaInput) {
        mediaInput.onchange = function () {
            if (this.files && this.files.length > 0) {
                this.form.submit();
            }
        };
    }

    // 4. Selection Management
    const toggleBtn = document.getElementById('toggle-selection-mode');
    const selectionBar = document.getElementById('selection-action-bar');
    const checkboxes = document.querySelectorAll('.message-checkbox');
    const placeholders = document.querySelectorAll('.selection-placeholder');
    const selectedCount = document.getElementById('selected-count');
    const selectedIdsContainer = document.getElementById('selected-ids-container');
    const cancelBtn = document.getElementById('cancel-selection');

    // Dropdown toggle logic
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownMenu = document.getElementById('dropdown-menu');

    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.onclick = (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        };

        document.addEventListener('click', (e) => {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('active');
            }
        });
    }

    // 5. Search Logic
    const searchBtn = document.getElementById('search-btn');
    const searchContainer = document.getElementById('search-container');
    const searchInput = document.getElementById('search-input');
    const closeSearch = document.getElementById('close-search');
    const messageRows = document.querySelectorAll('.group\\/row');
    const dateDividers = document.querySelectorAll('.flex.justify-center.my-8');

    if (searchBtn && searchContainer && searchInput) {
        searchBtn.onclick = () => {
            searchContainer.classList.remove('hidden');
            searchContainer.classList.add('flex');
            searchInput.focus();
        };

        const performSearch = () => {
            const query = searchInput.value.toLowerCase().trim();

            messageRows.forEach(row => {
                const text = row.querySelector('p')?.textContent.toLowerCase() || "";
                const isVisible = text.includes(query);
                row.classList.toggle('hidden', !isVisible);
            });

            // Hide empty date sections
            dateDividers.forEach(div => {
                let next = div.nextElementSibling;
                let hasVisibleMessages = false;
                while (next && !next.classList.contains('flex') && !next.querySelector('.date-divider')) {
                    if (next.classList.contains('group/row') && !next.classList.contains('hidden')) {
                        hasVisibleMessages = true;
                        break;
                    }
                    next = next.nextElementSibling;
                }
                div.classList.toggle('hidden', !hasVisibleMessages && query !== "");
            });
        };

        searchInput.oninput = performSearch;

        closeSearch.onclick = () => {
            searchContainer.classList.add('hidden');
            searchContainer.classList.remove('flex');
            searchInput.value = '';
            performSearch();
        };
    }

    let isSelectionMode = false;

    function updateSelection() {
        const checked = document.querySelectorAll('.message-checkbox:checked');
        if (selectedCount) selectedCount.textContent = checked.length;

        if (selectedIdsContainer) {
            selectedIdsContainer.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                selectedIdsContainer.appendChild(input);
            });
        }

        if (selectionBar) {
            if (isSelectionMode && checked.length > 0) {
                selectionBar.classList.remove('hidden');
                selectionBar.classList.add('flex');
            } else {
                selectionBar.classList.add('hidden');
                selectionBar.classList.remove('flex');
            }
        }
    }

    if (toggleBtn) {
        toggleBtn.onclick = function () {
            isSelectionMode = !isSelectionMode;
            checkboxes.forEach(cb => cb.classList.toggle('hidden', !isSelectionMode));
            placeholders.forEach(p => p.classList.toggle('hidden', !isSelectionMode));
            toggleBtn.textContent = isSelectionMode ? 'Terminer' : 'Sélectionner des messages';
            if (!isSelectionMode) {
                checkboxes.forEach(cb => cb.checked = false);
                updateSelection();
            }
            const dropdown = toggleBtn.closest('.dropdown-content');
            if (dropdown) dropdown.classList.remove('active');
        };
    }

    checkboxes.forEach(cb => cb.onchange = updateSelection);
    if (cancelBtn) {
        cancelBtn.onclick = function () {
            checkboxes.forEach(cb => cb.checked = false);
            updateSelection();
        };
    }

    // 6. Input Bar & Voice Recording
    const chatInput = document.getElementById('chat-input');
    const recordBtn = document.getElementById('record-btn');
    const submitBtn = document.getElementById('submit-btn');
    const micIcon = document.getElementById('mic-icon');
    const sendVocalIcon = document.getElementById('send-vocal-icon');
    const recordingOverlay = document.getElementById('recording-overlay');
    const recordingTimer = document.getElementById('recording-timer');
    const cancelRecordBtn = document.getElementById('cancel-record-btn');
    const chatForm = document.getElementById('chat-form');

    let mediaRecorder;
    let audioChunks = [];
    let startTime;
    let timerInterval;
    let isRecording = false;

    if (chatInput) {
        chatInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (!isRecording) {
                const hasText = this.value.trim().length > 0;
                recordBtn.classList.toggle('hidden', hasText);
                submitBtn.classList.toggle('hidden', !hasText);
            }
        });
    }

    async function startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);
            mediaRecorder.onstop = async () => {
                if (audioChunks.length > 0 && isRecording) {
                    const mimeType = mediaRecorder.mimeType || 'audio/webm';
                    const audioBlob = new Blob(audioChunks, { type: mimeType });
                    const formData = new FormData(chatForm);

                    // Déterminer l'extension
                    let ext = 'webm';
                    if (mimeType.includes('mp4')) ext = 'mp4';
                    else if (mimeType.includes('ogg')) ext = 'ogg';
                    else if (mimeType.includes('wav')) ext = 'wav';

                    // S'assurer que le message est vide mais présent
                    if (!formData.has('message')) formData.append('message', '');
                    formData.set('audio', audioBlob, `vocal.${ext}`);

                    try {
                        const response = await fetch(chatForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const errorData = await response.text();
                            console.error("Server error:", errorData);
                            alert("Erreur lors de l'envoi du vocal. Veuillez réessayer.");
                        }
                    } catch (err) {
                        console.error("Upload failed:", err);
                        alert("Échec de la connexion. Vérifiez votre internet.");
                    }
                }
                stream.getTracks().forEach(track => track.stop());
            };
            mediaRecorder.start();
            isRecording = true;
            startTime = Date.now();
            recordingOverlay.classList.remove('hidden');
            micIcon.classList.add('hidden');
            sendVocalIcon.classList.remove('hidden');
            recordBtn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'rounded-full');
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                recordingTimer.textContent = `${Math.floor(elapsed / 60).toString().padStart(2, '0')}:${(elapsed % 60).toString().padStart(2, '0')}`;
            }, 1000);
        } catch (err) { alert("Accès micro refusé."); }
    }

    function stopRecording(send = true) {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return;
        if (!send) isRecording = false;
        mediaRecorder.stop();
        clearInterval(timerInterval);
        recordingOverlay.classList.add('hidden');
        micIcon.classList.remove('hidden');
        sendVocalIcon.classList.add('hidden');
        recordBtn.classList.remove('bg-gray-100', 'dark:bg-gray-800');
        recordingTimer.textContent = '00:00';
    }

    if (recordBtn) recordBtn.onclick = () => !isRecording ? startRecording() : stopRecording(true);
    if (cancelRecordBtn) cancelRecordBtn.onclick = () => stopRecording(false);
});

// Vocal Player Definition
function initVocalPlayers() {
    document.querySelectorAll('.vocal-player').forEach(player => {
        if (player.dataset.initialized) return;
        player.dataset.initialized = "true";
        const audio = player.querySelector('.hidden-audio');
        const playBtn = player.querySelector('.vocal-play-btn');
        const playIcon = player.querySelector('.play-icon');
        const pauseIcon = player.querySelector('.pause-icon');
        const progressBar = player.querySelector('.vocal-progress-bar');
        const progressContainer = player.querySelector('.vocal-progress');
        const timeDisplay = player.querySelector('.vocal-time');
        const durationDisplay = player.querySelector('.vocal-duration');

        const formatTime = (s) => isNaN(s) || s === Infinity ? "0:00" : `${Math.floor(s / 60)}:${Math.floor(s % 60).toString().padStart(2, '0')}`;
        const updateDur = () => {
            if (audio.duration && audio.duration !== Infinity) durationDisplay.textContent = formatTime(audio.duration);
            else if (audio.duration === Infinity) {
                audio.currentTime = 1e101;
                audio.ontimeupdate = function () { this.ontimeupdate = null; this.currentTime = 0; durationDisplay.textContent = formatTime(this.duration); };
            }
        };
        audio.addEventListener('canplay', updateDur);
        audio.addEventListener('timeupdate', () => {
            if (audio.duration && audio.duration !== Infinity) progressBar.style.width = `${(audio.currentTime / audio.duration) * 100}%`;
            timeDisplay.textContent = formatTime(audio.currentTime);
        });
        audio.addEventListener('ended', () => { playIcon.classList.remove('hidden'); pauseIcon.classList.add('hidden'); progressBar.style.width = '0%'; timeDisplay.textContent = '0:00'; });
        playBtn.onclick = async (e) => {
            e.preventDefault(); e.stopPropagation();
            if (audio.paused) {
                document.querySelectorAll('.hidden-audio').forEach(a => { if (a !== audio && !a.paused) { a.pause(); const p = a.closest('.vocal-player'); if (p) { p.querySelector('.play-icon').classList.remove('hidden'); p.querySelector('.pause-icon').classList.add('hidden'); } } });
                await audio.play(); playIcon.classList.add('hidden'); pauseIcon.classList.remove('hidden');
            } else { audio.pause(); playIcon.classList.remove('hidden'); pauseIcon.classList.add('hidden'); }
        };
        progressContainer.onclick = (e) => { if (audio.duration && audio.duration !== Infinity) audio.currentTime = ((e.clientX - progressContainer.getBoundingClientRect().left) / progressContainer.offsetWidth) * audio.duration; };
    });
}
