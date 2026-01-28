<?php
/**
 * Vue Chat Global (Support)
 */
?>

<div class="max-w-5xl mx-auto">
    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/95 p-4 backdrop-blur-md transition-all duration-300">
        <button id="close-modal-btn" onclick="closeChatImage()" class="absolute top-6 right-6 z-[110] flex items-center gap-2 px-5 py-3 bg-red-500 hover:bg-red-600 text-white rounded-full transition-all shadow-2xl transform active:scale-95 group">
            <span class="text-sm font-black uppercase tracking-wider">Fermer</span>
            <svg class="w-6 h-6 border-l border-white/20 pl-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="max-w-4xl max-h-full relative flex items-center justify-center" onclick="event.stopPropagation()">
            <img id="modal-image" src="" alt="Aperçu" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain border border-white/10">
        </div>
    </div>

    <script>
        // Global functions for absolute reliability
        window.openChatImage = function(src, e) {
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

        window.closeChatImage = function() {
            const modal = document.getElementById('image-preview-modal');
            const modalImg = document.getElementById('modal-image');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (modalImg) modalImg.src = '';
                document.body.style.overflow = '';
            }
        };

        // Attach to overlay click
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('image-preview-modal');
            if (modal) {
                modal.onclick = function(e) {
                    if (e.target === modal) window.closeChatImage();
                };
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') window.closeChatImage();
            });
        });
    </script>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <?php if (isset($isAdminView) && $isAdminView): ?>
                <a href="<?= BASE_URL ?>/support/admin" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition flex items-center gap-2">
                    ← Retour aux conversations
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/dashboard" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition flex items-center gap-2">
                    ← Retour au dashboard
                </a>
            <?php endif; ?>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">
                <?= (isset($isAdminView) && $isAdminView) ? 'Discussion avec ' . htmlspecialchars($membre['designation']) : 'Chat de Support' ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Posez vos questions ou signalez des problèmes directement à l'administration.
            </p>
        </div>
    </div>

    <!-- Section Chat -->
    <div class="flex flex-col h-[650px] bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden transition-colors">
        <!-- Messages List -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div class="text-center py-20 bg-white/50 dark:bg-gray-900/50 rounded-3xl m-4">
                    <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.827-1.213L3 20l1.391-3.987A9 9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Posez vos questions ici ! Commencez la discussion.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <?php $isMe = ($m['sender_id'] == $currentUser['id']); ?>
                    <div class="flex flex-col <?= $isMe ? 'items-end' : 'items-start' ?>">
                        <div class="max-w-[80%] md:max-w-[70%] relative group">
                            <!-- Bubble -->
                            <div class="px-4 py-2.5 shadow-sm transition-colors 
                                <?= $isMe 
                                    ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl rounded-tr-none' 
                                    : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-2xl rounded-tl-none border border-gray-100 dark:border-gray-700' ?>">
                                
                                <?php if (!$isMe): ?>
                                    <span class="block text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1">
                                        <?= htmlspecialchars($m['sender_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($m['message'])): ?>
                                    <p class="text-[13px] leading-relaxed"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($m['image_path']): ?>
                                    <a href="<?= BASE_URL ?>/<?= $m['image_path'] ?>" onclick="return window.openChatImage(this.href, event)" class="chat-image-link block mt-2 rounded-lg overflow-hidden border border-black/5 relative group">
                                        <img src="<?= BASE_URL ?>/<?= $m['image_path'] ?>" alt="Image jointe" class="max-w-full h-auto transition group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center pointer-events-none">
                                            <span class="text-white text-[10px] font-bold uppercase tracking-wider">Voir en plein écran</span>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <?php if ($m['audio_path']): ?>
                                    <div class="mt-2 vocal-player-container w-full">
                                        <div class="vocal-player flex items-center gap-3 p-2 bg-black/5 dark:bg-white/5 rounded-2xl min-w-[220px]" data-audio-src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>">
                                            <button type="button" class="vocal-play-btn w-12 h-12 rounded-full bg-[#4caf50] text-white flex items-center justify-center shrink-0 hover:bg-[#43a047] transition shadow-sm active:scale-95">
                                                <svg class="play-icon w-6 h-6 fill-current ml-1 pointer-events-none" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                <svg class="pause-icon hidden w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                            </button>
                                            <div class="flex-1 min-w-0 pr-1">
                                                <div class="vocal-progress h-1 bg-gray-300 dark:bg-gray-700 rounded-full relative cursor-pointer group/progress">
                                                    <div class="vocal-progress-bar h-full bg-[#4caf50] rounded-full w-0 transition-none relative">
                                                        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-[#4caf50] rounded-full shadow-md scale-0 group-hover/progress:scale-100 transition-transform"></div>
                                                    </div>
                                                </div>
                                                <div class="flex justify-between items-center text-[10px] opacity-70 mt-2 font-mono tracking-tighter">
                                                    <span class="vocal-time">0:00</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="vocal-duration">0:00</span>
                                                    </div>
                                                </div>
                                                <!-- Zone de Diagnostic (Visible uniquement en cas d'erreur) -->
                                                <div class="diagnostic-error hidden mt-2 p-1 bg-red-100 text-red-700 text-[8px] rounded border border-red-200 font-mono break-all"></div>
                                            </div>
                                            <audio class="hidden-audio" style="display:none" preload="auto">
                                                <source src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>" type="audio/webm">
                                                <source src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>" type="audio/ogg">
                                                <source src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>" type="audio/mp4">
                                            </audio>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="flex items-center justify-end mt-1 opacity-50">
                                    <span class="text-[9px] uppercase tracking-tighter">
                                        <?= date('H:i', strtotime($m['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Input -->
        <div class="p-2 md:p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 transition-colors">
            <form id="chat-form" action="<?= BASE_URL ?>/support/send" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                <input type="hidden" name="membre_id" value="<?= $membre['id'] ?>">
                
                <div class="flex gap-2 items-end">
                    <div id="input-container" class="flex-1 flex gap-2 items-end min-w-0">
                        <textarea name="message" id="chat-input" rows="1" placeholder="Message..." required
                            class="flex-1 min-w-0 px-3 md:px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl text-sm focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition resize-none"></textarea>
                        
                        <label class="flex-shrink-0 p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm" title="Joindre une image">
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                        </label>
                    </div>

                    <!-- Interface Enregistrement (Hidden by default) -->
                    <div id="recording-overlay" class="hidden flex-1 flex items-center gap-2 md:gap-4 bg-gray-200 dark:bg-gray-800 rounded-2xl px-3 md:px-4 py-3 min-w-0">
                        <div class="flex items-center gap-1 md:gap-2 flex-shrink-0">
                            <div class="w-2 h-2 md:w-2.5 md:h-2.5 bg-red-500 rounded-full animate-pulse"></div>
                            <span id="recording-timer" class="text-xs md:text-sm font-bold text-gray-700 dark:text-gray-300">00:00</span>
                        </div>
                        <div class="flex-1 text-[10px] md:text-sm text-gray-500 italic truncate">Enregistrement...</div>
                        <button type="button" id="cancel-record-btn" class="flex-shrink-0 p-1 text-gray-500 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    
                    <div class="flex gap-1.5 md:gap-2 flex-shrink-0">
                        <button type="button" id="record-btn" class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm group" title="Vocal">
                            <svg id="mic-icon" class="w-5 h-5 md:w-6 md:h-6 text-gray-500 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                            <svg id="send-vocal-icon" class="hidden w-5 h-5 md:w-6 md:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                        
                        <button type="submit" id="submit-btn" class="p-3 bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition shadow-lg">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of chat
    const chatContainer = document.getElementById('chat-messages');
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Optional: Auto-submit on image choice
    document.querySelector('input[name="image"]').addEventListener('change', function() {
        if (this.files.length > 0) {
            this.form.submit();
        }
    });

    // Voice Recording Logic (WhatsApp Style)
    let mediaRecorder;
    let audioChunks = [];
    let startTime;
    let timerInterval;

    const recordBtn = document.getElementById('record-btn');
    const cancelBtn = document.getElementById('cancel-record-btn');
    const micIcon = document.getElementById('mic-icon');
    const sendVocalIcon = document.getElementById('send-vocal-icon');
    const recordingOverlay = document.getElementById('recording-overlay');
    const inputContainer = document.getElementById('input-container');
    const timerDisplay = document.getElementById('recording-timer');
    const chatInput = document.getElementById('chat-input');
    const submitBtn = document.getElementById('submit-btn');

    function updateTimer() {
        const now = new Date();
        const diff = new Date(now - startTime);
        const mins = diff.getUTCMinutes().toString().padStart(2, '0');
        const secs = diff.getUTCSeconds().toString().padStart(2, '0');
        timerDisplay.textContent = `${mins}:${secs}`;
    }

    function getSupportedMimeType() {
        const types = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/ogg',
            'audio/mp4',
            'audio/aac'
        ];
        for (const type of types) {
            if (MediaRecorder.isTypeSupported(type)) {
                console.log("Supported MIME type found:", type);
                return type;
            }
        }
        return '';
    }

    recordBtn.addEventListener('click', async () => {
        console.log("Record button clicked in support");
        // Start Recording
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const supportedType = getSupportedMimeType();
                mediaRecorder = new MediaRecorder(stream, supportedType ? { mimeType: supportedType } : {});
                audioChunks = [];
                const currentMimeType = supportedType || 'audio/webm';

                mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);

                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: currentMimeType });
                    const formData = new FormData();
                    const extension = currentMimeType.includes('ogg') ? 'ogg' : 
                                    currentMimeType.includes('mp4') ? 'mp4' : 'webm';
                    formData.append('audio', audioBlob, `vocal.${extension}`);
                    formData.append('membre_id', '<?= $membre['id'] ?>');
                    formData.append('message', '');

                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50');

                    try {
                        const response = await fetch('<?= BASE_URL ?>/support/send', {
                            method: 'POST',
                            body: formData
                        });
                        if (response.ok) window.location.reload();
                        else alert('Erreur d\'envoi');
                    } catch (err) {
                        alert('Erreur réseau');
                    }
                };

                mediaRecorder.start();
                startTime = new Date();
                timerInterval = setInterval(updateTimer, 1000);
                timerDisplay.textContent = "00:00";

                // UI Toggle
                inputContainer.classList.add('hidden');
                submitBtn.classList.add('hidden');
                recordingOverlay.classList.remove('hidden');
                micIcon.classList.add('hidden');
                sendVocalIcon.classList.remove('hidden');
                recordBtn.classList.add('bg-gray-100', 'dark:bg-gray-800');

            } catch (err) {
                alert('Microphone non autorisé.');
            }
        } 
        // Stop and Send
        else {
            clearInterval(timerInterval);
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }
    });

    cancelBtn.addEventListener('click', () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            clearInterval(timerInterval);
            mediaRecorder.onstop = null; // Don't send
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            
            // Reset UI
            inputContainer.classList.remove('hidden');
            submitBtn.classList.remove('hidden');
            recordingOverlay.classList.add('hidden');
            micIcon.classList.remove('hidden');
            sendVocalIcon.classList.add('hidden');
            recordBtn.classList.remove('bg-gray-100', 'dark:bg-gray-800');
        }
    });

    // Enter to send (Shift+Enter for newline)
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (this.value.trim() !== '') {
                this.form.submit();
            }
        }
    });

    // Custom Audio Player Logic

    // Custom Audio Player Logic

    // Custom Audio Player Logic
    function initVocalPlayers() {
        console.log("Vocal Players Initialization...");
        const players = document.querySelectorAll('.vocal-player');
        console.log("Found " + players.length + " players");
        
        players.forEach((player, index) => {
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

            console.log("Configuring player #" + index, audio.src);
            
            // Force load
            audio.load();

            function formatTime(seconds) {
                if (isNaN(seconds) || seconds === Infinity) return "0:00";
                const min = Math.floor(seconds / 60);
                const sec = Math.floor(seconds % 60);
                return `${min}:${sec.toString().padStart(2, '0')}`;
            }

            // Sync total duration
            const updateDuration = () => {
                console.log("Metadata/Canplay for #" + index + ", duration:", audio.duration);
                if (audio.duration && audio.duration !== Infinity) {
                    durationDisplay.textContent = formatTime(audio.duration);
                    durationDisplay.classList.remove('text-red-500');
                } else if (audio.duration === Infinity) {
                    // Hack for Chrome/WebM: seek to the end to find duration
                    console.log("Infinity duration detected, attempting hack...");
                    audio.currentTime = 1e101;
                    audio.addEventListener('timeupdate', function getDuration() {
                        audio.currentTime = 0;
                        audio.removeEventListener('timeupdate', getDuration);
                    }, { once: true });
                }
            };

            audio.addEventListener('loadedmetadata', updateDuration);
            audio.addEventListener('canplay', updateDuration);

            // Debug listeners
            audio.addEventListener('play', () => console.log("Audio #" + index + " playing"));
            audio.addEventListener('pause', () => console.log("Audio #" + index + " paused"));
            audio.addEventListener('waiting', () => console.log("Audio #" + index + " waiting..."));
            audio.addEventListener('stalled', () => console.warn("Audio #" + index + " stalled"));
            
            audio.addEventListener('error', (e) => {
                const error = audio.error;
                let msg = "Erreur audio inconnue";
                let code = 0;
                if (error) {
                    code = error.code;
                    switch(error.code) {
                        case 1: msg = "Aborted"; break;
                        case 2: msg = "Network error"; break;
                        case 3: msg = "Decode error"; break;
                        case 4: msg = "404 - Fichier non trouvé"; break;
                    }
                }
                console.error("Audio error #" + index + " (Code " + code + "):", msg, audio.src);
                durationDisplay.textContent = "Err " + code;
                durationDisplay.classList.add('text-red-500');
                
                // Retry logic if source not found but maybe just slow
                if (error && error.code === 4) {
                    setTimeout(() => {
                        if (audio.readyState === 0) audio.load();
                    }, 3000);
                }
            });

            if (audio.duration && audio.duration !== Infinity) {
                durationDisplay.textContent = formatTime(audio.duration);
            }

            // Update progress
            audio.addEventListener('timeupdate', () => {
                if (audio.duration && audio.duration !== Infinity) {
                    const percent = (audio.currentTime / audio.duration) * 100;
                    progressBar.style.width = `${percent}%`;
                }
                timeDisplay.textContent = formatTime(audio.currentTime);
            });

            // Handle end
            audio.addEventListener('ended', () => {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
                progressBar.style.width = '0%';
                timeDisplay.textContent = '0:00';
            });

            // Play/Pause toggle
            playBtn.onclick = async function(e) {
                console.log("Play button clicked for #" + index);
                const diagnostic = player.querySelector('.diagnostic-error');
                if (diagnostic) {
                    diagnostic.classList.add('hidden');
                    diagnostic.textContent = '';
                }

                e.preventDefault();
                e.stopPropagation();

                try {
                    if (audio.paused) {
                        // Stop other players
                        document.querySelectorAll('.hidden-audio').forEach(otherAudio => {
                            if (otherAudio !== audio && !otherAudio.paused) {
                                otherAudio.pause();
                                const otherPlayer = otherAudio.closest('.vocal-player');
                                if (otherPlayer) {
                                    otherPlayer.querySelector('.play-icon').classList.remove('hidden');
                                    otherPlayer.querySelector('.pause-icon').classList.add('hidden');
                                }
                            }
                        });

                        const playPromise = audio.play();
                        
                        if (playPromise !== undefined) {
                            playPromise.then(() => {
                                console.log("Play success for #" + index);
                                playIcon.classList.add('hidden');
                                pauseIcon.classList.remove('hidden');
                            }).catch(err => {
                                console.error("Play failed for #" + index + ":", err);
                                if (diagnostic) {
                                    diagnostic.textContent = "Erreur lecture: " + err.message;
                                    diagnostic.classList.remove('hidden');
                                }
                            });
                        }
                    } else {
                        console.log("Pausing #" + index);
                        audio.pause();
                        playIcon.classList.remove('hidden');
                        pauseIcon.classList.add('hidden');
                    }
                } catch (err) {
                    console.error("Critical play error #" + index + ":", err);
                    if (diagnostic) {
                        diagnostic.textContent = "Erreur critique: " + err.name;
                        diagnostic.classList.remove('hidden');
                    }
                }
            };

            // Seek
            progressContainer.addEventListener('click', (e) => {
                if (!audio.duration || audio.duration === Infinity) return;
                const rect = progressContainer.getBoundingClientRect();
                const pos = (e.clientX - rect.left) / rect.width;
                audio.currentTime = pos * audio.duration;
            });
        });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initVocalPlayers);
</script>

