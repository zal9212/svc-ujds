<?php
/**
 * Vue Chat Support
 */
?>



<link rel="stylesheet" href="<?= BASE_URL ?>/css/chat.css">

<div class="max-w-7xl mx-auto">
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

    <!-- Dashboard Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Sidebar: Détails de la Discussion -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden sticky top-24">
                <!-- Header Sidebar -->
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Informations</h3>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Participant -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-4">
                            <a href="<?= (isset($isAdminView) && $isAdminView) ? BASE_URL . '/membres/show?id=' . $membre['id'] : BASE_URL . '/membres/profile' ?>" class="block group">
                                <?php if (!empty($membre['photo_profil'])): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($membre['photo_profil']) ?>" class="w-24 h-24 rounded-3xl object-cover ring-4 ring-gray-100 dark:ring-gray-800 shadow-lg group-hover:ring-blue-500 transition-all">
                                <?php else: ?>
                                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg ring-4 ring-gray-100 dark:ring-gray-800 group-hover:ring-blue-500 transition-all">
                                        <?= strtoupper(substr($membre['designation'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-green-500 border-4 border-white dark:border-gray-900 shadow-sm"></div>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['designation']) ?></h4>
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest rounded-full mt-2">
                            <?= ($membre['statut'] === 'ACTIF') ? 'Membre Actif' : htmlspecialchars($membre['statut']) ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                            <span class="block text-[10px] font-black text-gray-500 uppercase tracking-tighter mb-1">Code</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['code']) ?></span>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                            <span class="block text-[10px] font-black text-gray-500 uppercase tracking-tighter mb-1">Civilité</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['titre'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <!-- Contact Quick Info -->
                    <?php if ($membre['telephone']): ?>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                        <span class="block text-[10px] font-black text-gray-500 uppercase tracking-tighter mb-1">Téléphone</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['telephone']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="pt-4 flex flex-col gap-2">
                        <a href="<?= (isset($isAdminView) && $isAdminView) ? BASE_URL . '/support/admin' : BASE_URL . '/dashboard' ?>" 
                           class="w-full text-center py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <?= (isset($isAdminView) && $isAdminView) ? 'Retour aux discussions' : 'Retour au dashboard' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone Chat -->
        <div class="lg:col-span-2 flex flex-col h-[750px] bg-white dark:bg-[#0b141a] rounded-3xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden transition-colors">
            
            <!-- Header Chat -->
            <div class="flex items-center justify-between bg-white/80 dark:bg-[#202c33]/80 backdrop-blur-md px-6 py-4 border-b border-gray-200 dark:border-gray-800 z-10">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                            <?= (isset($isAdminView) && $isAdminView) ? 'Discussion avec ' . htmlspecialchars($membre['designation']) : 'Support Client SVC-UJDS' ?>
                        </h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-sm"></span>
                            <span class="text-[10px] text-green-500 font-black uppercase tracking-widest">En direct</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Search Container (Floats inside header) -->
                    <div id="search-container" class="hidden items-center bg-gray-100 dark:bg-gray-800 rounded-2xl px-3 py-1.5 animate-in slide-in-from-right-2 duration-200 min-w-[200px] md:min-w-[280px]">
                        <input type="text" id="search-input" placeholder="Rechercher..." class="bg-transparent border-none text-xs focus:ring-0 w-full dark:text-white py-1">
                        <button id="close-search" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <button id="search-btn" class="p-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition shadow-sm bg-gray-50 dark:bg-gray-800/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>

                    <div class="dropdown">
                        <button id="dropdown-toggle" class="p-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition shadow-sm bg-gray-50 dark:bg-gray-800/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </button>
                        <div id="dropdown-menu" class="dropdown-content mt-2 shadow-2xl border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden py-1">
                            <button id="toggle-selection-mode" class="w-full text-left flex items-center gap-3 px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Sélectionner des messages
                            </button>
                            <?php if (isset($isAdminView) && $isAdminView): ?>
                                <form action="<?= BASE_URL ?>/support/clear-chat" method="POST" onsubmit="return confirm('Attention ! Cette action supprimera TOUS les messages et fichiers de cette discussion. Continuer ?')" class="block border-t border-gray-50 dark:border-gray-800">
                                    <input type="hidden" name="membre_id" value="<?= $membre['id'] ?>">
                                    <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-3 text-xs font-black text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Effacer la discussion
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages List -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-4 chat-bg custom-scrollbar" id="chat-messages">
                <?php if (empty($messages)): ?>
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center p-8 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700 max-w-sm">
                            <div class="w-16 h-16 bg-white dark:bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            </div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-2">Nouvelle Discussion</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Commencez la discussion ! Envoyez un message ou une question au support.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $lastDate = null;
                    foreach ($messages as $index => $m): 
                        $isMe = ($m['sender_id'] == $currentUser['id']);
                        $currentDate = date('Y-m-d', strtotime($m['created_at']));
                        $displayDate = "";
                        
                        if ($currentDate !== $lastDate) {
                            if ($currentDate === date('Y-m-d')) $displayDate = "Aujourd'hui";
                            elseif ($currentDate === date('Y-m-d', strtotime('-1 day'))) $displayDate = "Hier";
                            else $displayDate = date('d/m/Y', strtotime($m['created_at']));
                            $lastDate = $currentDate;
                        }
                    ?>
                    
                    <?php if ($displayDate): ?>
                        <div class="flex justify-center my-8">
                            <span class="px-4 py-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-full text-[10px] font-black uppercase tracking-widest text-gray-500 shadow-sm border border-gray-200 dark:border-gray-700">
                                <?= $displayDate ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex items-start gap-3 w-full group/row">
                        <?php if ($currentUser['role'] === 'admin' || $isMe): ?>
                            <input type="checkbox" value="<?= $m['id'] ?>" class="message-checkbox hidden mt-2 w-5 h-5 rounded-full border-2 border-gray-300 text-red-500 focus:ring-red-500 transition-all cursor-pointer shrink-0 appearance-none checked:bg-red-500 checked:border-red-500 relative before:content-[''] before:absolute before:inset-0 before:flex before:items-center before:justify-center before:text-white before:text-[10px] before:font-black checked:before:content-['✓']">
                        <?php else: ?>
                            <div class="selection-placeholder hidden w-5 shrink-0"></div>
                        <?php endif; ?>

                        <div class="flex flex-col flex-1 <?= $isMe ? 'items-end' : 'items-start' ?>">
                            <div class="max-w-[85%] md:max-w-[70%] relative group">
                                <!-- Bubble -->
                                <div class="px-4 py-3 shadow-xl transition-all relative 
                                    <?= $isMe 
                                        ? 'bubble-out rounded-2xl rounded-tr-none text-white' 
                                        : 'bubble-in rounded-2xl rounded-tl-none text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-gray-800' ?>">
                                    
                                    <?php if (!$isMe): ?>
                                        <span class="block text-[10px] font-black uppercase tracking-widest text-blue-500 mb-1.5">
                                            <?= htmlspecialchars($m['sender_name']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($m['message'])): ?>
                                        <p class="text-[14px] font-medium leading-relaxed"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($m['image_path']): ?>
                                        <div onclick="window.openChatImage('<?= BASE_URL ?>/<?= $m['image_path'] ?>', event)" class="chat-image-link block mt-2 rounded-xl overflow-hidden border border-black/5 dark:border-white/5 cursor-pointer hover:opacity-95 transition-opacity shadow-sm bg-gray-50 dark:bg-gray-900">
                                            <img src="<?= BASE_URL ?>/<?= $m['image_path'] ?>" alt="Image jointe" class="max-w-full h-auto">
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($m['audio_path']): ?>
                                        <div class="mt-3 vocal-player-container w-full min-w-[200px]">
                                            <div class="vocal-player flex items-center gap-3 p-2 bg-black/10 dark:bg-white/10 rounded-2xl" data-audio-src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>">
                                                <button type="button" class="vocal-play-btn w-10 h-10 rounded-xl bg-white dark:bg-gray-900 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-lg active:scale-95 transition-transform">
                                                    <svg class="play-icon w-6 h-6 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    <svg class="pause-icon hidden w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                </button>
                                                <div class="flex-1 min-w-0 pr-1">
                                                    <div class="vocal-progress h-1 bg-white/30 dark:bg-gray-700/50 rounded-full relative cursor-pointer">
                                                        <div class="vocal-progress-bar h-full bg-blue-500 rounded-full w-0 transition-none"></div>
                                                    </div>
                                                    <div class="flex justify-between items-center text-[9px] font-black mt-1.5 opacity-80 uppercase tracking-tighter">
                                                        <span class="vocal-time">0:00</span>
                                                        <span class="vocal-duration">0:00</span>
                                                    </div>
                                                </div>
                                                <audio class="hidden-audio" style="display:none" preload="auto">
                                                    <?php 
                                                    $ext = pathinfo($m['audio_path'], PATHINFO_EXTENSION);
                                                    $mime = match($ext) {
                                                        'mp4', 'm4a' => 'audio/mp4',
                                                        'ogg' => 'audio/ogg',
                                                        'wav' => 'audio/wav',
                                                        default => 'audio/webm'
                                                    };
                                                    ?>
                                                    <source src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>" type="<?= $mime ?>">
                                                </audio>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex items-center justify-end gap-1.5 mt-2 opacity-60">
                                        <span class="text-[9px] font-black tracking-widest uppercase">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </span>
                                        <?php if ($isMe): ?>
                                            <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M18,7l-1.41-1.41-6.34,6.34 1.41,1.41L18,7zm4.24-4.24l-1.41-1.41L10.24,11.93l1.41,1.41L22.24,2.76zM7.16,16.5l-4.24-4.24-1.41,1.41 5.66,5.66 1.41-1.41L7.16,16.5z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Selection Action Bar -->
            <div id="selection-action-bar" class="hidden px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 items-center justify-between animate-in slide-in-from-bottom duration-300">
                <div class="flex flex-col gap-0.5">
                    <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest leading-none"><span id="selected-count">0</span> sélectionné(s)</span>
                    <button id="cancel-selection" class="text-[10px] font-bold text-gray-500 hover:text-red-500 transition-colors inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Annuler la sélection
                    </button>
                </div>
                <form action="<?= BASE_URL ?>/support/delete-multiple" method="POST" id="delete-form" onsubmit="return confirm('Souhaitez-vous vraiment supprimer les messages sélectionnés ?')">
                    <div id="selected-ids-container"></div>
                    <button type="submit" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg active:scale-95 flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Supprimer
                    </button>
                </form>
            </div>

            <!-- Chat Input -->
            <div class="px-4 py-4 bg-gray-50 dark:bg-[#111b21] border-t border-gray-100 dark:border-gray-800 transition-colors shadow-2xl">
                <form id="chat-form" action="<?= BASE_URL ?>/support/send" method="POST" enctype="multipart/form-data" class="flex items-center gap-4 max-w-5xl mx-auto">
                    <input type="hidden" name="membre_id" value="<?= $membre['id'] ?>">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    
                    <div class="flex items-center gap-1 group">
                        <label class="p-3 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-2xl cursor-pointer transition-all active:scale-90" title="Joindre une image">
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </label>
                    </div>

                    <div class="relative flex-1 flex items-center">
                        <div id="input-container" class="flex-1">
                            <textarea name="message" id="chat-input" rows="1" placeholder="Votre message..."
                                class="w-full px-5 py-3 bg-white dark:bg-[#2a3942] border border-gray-200 dark:border-none rounded-2xl text-[14px] font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 transition-all resize-none placeholder-gray-400 dark:text-white shadow-sm"></textarea>
                        </div>

                        <!-- Recording Overlay -->
                        <div id="recording-overlay" class="hidden absolute inset-0 bg-white dark:bg-[#202c33] border border-red-100 dark:border-red-900/30 rounded-2xl flex items-center px-4 gap-4 z-10 animate-in fade-in zoom-in-95 duration-200 shadow-inner">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shadow-sm shadow-red-500/50"></div>
                                <span id="recording-timer" class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest tabular-nums">00:00</span>
                            </div>
                            <div class="flex-1 text-[11px] font-bold text-gray-500 uppercase tracking-wider truncate italic">Enregistrement vocal en cours...</div>
                            <button type="button" id="cancel-record-btn" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all active:scale-90">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <button type="button" id="record-btn" class="p-3.5 bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-2xl transition-all group active:scale-95 shadow-sm" title="Vocal">
                            <svg id="mic-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                            <svg id="send-vocal-icon" class="hidden w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                        
                        <button type="submit" id="submit-btn" class="hidden p-3.5 bg-blue-500 text-white rounded-2xl hover:bg-blue-600 hover:shadow-lg hover:shadow-blue-500/30 transition-all active:scale-95 shadow-md">
                            <svg class="w-6 h-6 rotate-[-15deg] transform translate-y-[-1px] translate-x-[-1px]" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/chat.js"></script>

