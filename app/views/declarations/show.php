<?php
/**
 * Vue Détail de la Déclaration + Chat
 */
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/chat.css">

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

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <a href="<?= in_array($currentUser['role'], ['admin', 'comptable']) ? BASE_URL . '/declarations/admin' : BASE_URL . '/declarations' ?>" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition flex items-center gap-2">
                ← Retour à la liste
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Déclaration #<?= $declaration['id'] ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Soumise par <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($declaration['designation']) ?></span> le <?= date('d/m/Y à H:i', strtotime($declaration['created_at'])) ?>
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <?php
            $statusClass = match($declaration['statut']) {
                'EN_ATTENTE' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                'VALIDE' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                'REJETE' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                default => 'bg-gray-100 text-gray-800'
            };
            ?>
            <span class="px-6 py-3 rounded-2xl text-sm font-black <?= $statusClass ?> uppercase tracking-widest shadow-sm">
                <?= str_replace('_', ' ', $declaration['statut']) ?>
            </span>
        </div>
    </div>

    <!-- Unified Dashboard Container -->
    <div class="bg-white dark:bg-[#0b141a] rounded-[2.5rem] border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col lg:flex-row h-[85vh] transition-all duration-500">
        
        <!-- Sidebar: Payment Details (Sidebar) -->
        <div class="w-full lg:w-80 xl:w-96 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-orange-500/10 flex flex-col bg-gray-50/50 dark:bg-[#111b21] shrink-0">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-800/50 bg-white/50 dark:bg-transparent backdrop-blur-xl">
                <h2 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-1">Détails du Paiement</h2>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full <?= $declaration['statut'] === 'VALIDE' ? 'bg-green-500' : ($declaration['statut'] === 'REJETE' ? 'bg-red-500' : 'bg-orange-500 animate-pulse') ?>"></div>
                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase"><?= str_replace('_', ' ', $declaration['statut']) ?></span>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                <!-- Montant -->
                <div class="group">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest mb-2 transition-colors group-hover:text-blue-500">Montant Déclaré</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-gray-900 dark:text-white tracking-tight"><?= number_format($declaration['montant'], 0, ',', ' ') ?></span>
                        <span class="text-xs font-bold text-gray-500">FCFA</span>
                    </div>
                </div>

                <!-- Type -->
                <div class="group">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest mb-2 transition-colors group-hover:text-blue-500">Type de Versement</p>
                    <div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-100 dark:border-blue-800/50">
                        <?php
                        echo match($declaration['type_paiement']) {
                            'mois_en_cours' => 'Mois en cours',
                            'dette_anterieure' => 'Dette antérieure',
                            'avance_mois' => 'Anticipation',
                            'avance_annee' => 'Avance année',
                            'anticipation' => 'Anticipation',
                            default => $declaration['type_paiement']
                        };
                        ?>
                    </div>
                </div>

                <!-- Preuve -->
                <?php if ($declaration['preuve_path']): ?>
                <div class="group">
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest mb-3 transition-colors group-hover:text-blue-500">Preuve de Paiement</p>
                    <a href="<?= BASE_URL ?>/<?= $declaration['preuve_path'] ?>" onclick="return window.openChatImage(this.href, event)" class="chat-image-link block group/img relative rounded-2xl overflow-hidden border-2 border-white dark:border-gray-800 shadow-lg bg-white dark:bg-gray-800 aspect-video">
                        <img src="<?= BASE_URL ?>/<?= $declaration['preuve_path'] ?>" alt="Preuve" class="w-full h-full object-cover transition-transform duration-700 group-hover/img:scale-110">
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Admin Action Card -->
                <?php if (in_array($currentUser['role'], ['admin', 'comptable']) && $declaration['statut'] === 'EN_ATTENTE'): ?>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800/50 space-y-3">
                    <form action="<?= BASE_URL ?>/declarations/validate" method="POST" onsubmit="return confirm('Confirmez-vous la validité de ce paiement ?');">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $declaration['id'] ?>">
                        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                            Valider le Paiement
                        </button>
                    </form>
                    <form action="<?= BASE_URL ?>/declarations/reject" method="POST" onsubmit="return confirm('Voulez-vous rejeter cette déclaration ?');">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $declaration['id'] ?>">
                        <button type="submit" class="w-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 py-3 rounded-2xl font-bold text-[10px] uppercase tracking-widest transition-all active:scale-95">
                            Rejeter
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Area: Chat -->
        <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-[#0b141a]">
            <!-- Chat Header -->
            <div class="flex items-center justify-between bg-white/80 dark:bg-[#202c33]/90 backdrop-blur-md px-6 py-4 border-b border-gray-100 dark:border-gray-800/50 z-20">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white leading-none">Espace de Discussion</h3>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-[10px] text-green-500 font-black uppercase tracking-widest">En direct</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Search Container -->
                    <div id="search-container" class="hidden items-center bg-gray-100 dark:bg-gray-800 rounded-full px-4 py-2 transition-all duration-300">
                        <input type="text" id="search-input" placeholder="Rechercher..." class="bg-transparent border-none text-sm focus:ring-0 w-40 md:w-64 dark:text-white py-0">
                        <button id="close-search" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <button id="search-btn" class="p-3 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-2xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>

                    <div class="dropdown">
                        <button id="dropdown-toggle" class="p-3 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-2xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </button>
                        <div id="dropdown-menu" class="dropdown-content shadow-2xl border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden mt-2">
                            <button id="toggle-selection-mode" class="text-left px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-300 transition-colors w-full">Sélectionner des messages</button>
                            
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <form action="<?= BASE_URL ?>/declarations/clear-chat" method="POST" onsubmit="return confirm('Attention ! Effacer toute la discussion ?')" class="block">
                                    <input type="hidden" name="declaration_id" value="<?= $declaration['id'] ?>">
                                    <button type="submit" class="w-full text-left px-5 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-bold transition-colors">
                                        Effacer la discussion
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            

            <!-- Messages List -->
            <div class="flex-1 overflow-y-auto p-4 md:p-10 space-y-6 chat-bg" id="chat-messages">
                <?php if (empty($messages)): ?>
                    <div class="flex flex-col items-center justify-center py-20 opacity-40">
                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-800 rounded-[2.5rem] flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.827-1.213L3 20l1.391-3.987A9 9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <p class="text-lg font-bold text-gray-500 dark:text-gray-400">Aucun message pour le moment</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Posez vos questions sur ce paiement ici.</p>
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
                            <span class="date-divider px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em] text-gray-500/80 dark:text-gray-400/50 shadow-sm border border-gray-100 dark:border-gray-800/50">
                                <?= $displayDate ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex items-start gap-4 w-full group/row">
                        <?php if ($currentUser['role'] === 'admin' || $isMe): ?>
                            <input type="checkbox" value="<?= $m['id'] ?>" class="message-checkbox hidden w-5 h-5 mt-2 rounded-lg border-2 border-gray-300 text-blue-500 focus:ring-blue-500 transition-all cursor-pointer shrink-0 appearance-none checked:bg-blue-500 checked:border-blue-500 relative before:content-[''] before:absolute before:inset-0 before:flex before:items-center before:justify-center before:text-white before:text-[10px] before:font-black checked:before:content-['✓']">
                        <?php else: ?>
                            <div class="selection-placeholder hidden w-5 shrink-0"></div>
                        <?php endif; ?>

                        <div class="flex flex-col flex-1 <?= $isMe ? 'items-end' : 'items-start' ?>">
                            <div class="max-w-[85%] md:max-w-[70%] relative group">
                                <div class="px-4 py-3 shadow-xl transition-all relative border <?= $isMe 
                                        ? 'bg-blue-600 border-blue-500 text-white rounded-[1.5rem] rounded-tr-none' 
                                        : 'bg-white dark:bg-[#202c33] border-gray-100 dark:border-gray-800 text-gray-900 dark:text-gray-100 rounded-[1.5rem] rounded-tl-none' ?>">
                                    
                                    <?php if (!$isMe): ?>
                                        <span class="block text-[10px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1.5">
                                            <?= htmlspecialchars($m['sender_name'] ?? 'Admin') ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($m['message'])): ?>
                                        <p class="text-[14px] leading-relaxed font-medium"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($m['image_path']): ?>
                                        <a href="<?= BASE_URL ?>/<?= $m['image_path'] ?>" onclick="return window.openChatImage(this.href, event)" class="chat-image-link block mt-3 rounded-2xl overflow-hidden border-2 border-black/5 relative group/image shadow-md">
                                            <img src="<?= BASE_URL ?>/<?= $m['image_path'] ?>" alt="Image" class="max-w-full h-auto transition duration-500 group-hover/image:scale-110">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/image:opacity-100 transition-opacity flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </div>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($m['audio_path']): ?>
                                        <div class="mt-3 vocal-player-container w-full">
                                            <div class="vocal-player flex items-center gap-3 p-3 <?= $isMe ? 'bg-white/10' : 'bg-gray-50 dark:bg-black/20' ?> rounded-2xl" data-audio-src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>">
                                                <button type="button" class="vocal-play-btn w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 hover:bg-emerald-600 transition-all shadow-lg active:scale-90">
                                                    <svg class="play-icon w-5 h-5 fill-current ml-1" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    <svg class="pause-icon hidden w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                </button>
                                                <div class="flex-1">
                                                    <div class="vocal-progress h-1.5 <?= $isMe ? 'bg-white/30' : 'bg-gray-200 dark:bg-gray-700' ?> rounded-full overflow-hidden relative cursor-pointer">
                                                        <div class="vocal-progress-bar h-full bg-emerald-500 w-0"></div>
                                                    </div>
                                                    <div class="flex justify-between mt-2 text-[9px] font-black tracking-widest uppercase opacity-60">
                                                        <span class="vocal-time">0:00</span>
                                                        <span class="vocal-duration">0:00</span>
                                                    </div>
                                                </div>
                                                <audio class="hidden-audio" style="display:none" preload="auto">
                                                    <source src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>" type="audio/webm">
                                                </audio>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex items-center justify-end gap-1.5 mt-2 opacity-50">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-[inherit]"><?= date('H:i', strtotime($m['created_at'])) ?></span>
                                        <?php if ($isMe): ?>
                                            <div class="text-blue-100/50">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18,7l-1.41-1.41-6.34,6.34 1.41,1.41L18,7zm4.24-4.24l-1.41-1.41L10.24,11.93l1.41,1.41L22.24,2.76zM7.16,16.5l-4.24-4.24-1.41,1.41 5.66,5.66 1.41-1.41L7.16,16.5z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Selection Bar -->
            <div id="selection-action-bar" class="hidden px-6 py-3 bg-gray-900 text-white items-center justify-between z-30">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-black uppercase tracking-widest"><span id="selected-count">0</span> Sélectionné(s)</span>
                    <button id="cancel-selection" class="text-[10px] font-bold text-gray-400 hover:text-white underline transition">Annuler</button>
                </div>
                <form action="<?= BASE_URL ?>/declarations/delete-messages" method="POST" id="delete-form" onsubmit="return confirm('Supprimer ?')">
                    <div id="selected-ids-container"></div>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                        Supprimer
                    </button>
                </form>
            </div>

            <!-- Chat Input Bar -->
            <div class="p-6 bg-white dark:bg-[#202c33] border-t border-gray-100 dark:border-gray-800/50 z-20">
                <form id="chat-form" action="<?= BASE_URL ?>/declarations/message" method="POST" enctype="multipart/form-data" class="flex items-center gap-4 max-w-4xl mx-auto">
                    <input type="hidden" name="declaration_id" value="<?= $declaration['id'] ?>">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    
                    <div class="flex items-center">
                        <label class="p-3 text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 cursor-pointer transition-colors relative group" title="Image">
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </label>
                    </div>

                    <div class="relative flex-1">
                        <textarea name="message" id="chat-input" rows="1" placeholder="Tapez votre message ici..."
                            class="w-full px-5 py-3.5 bg-gray-50 dark:bg-[#2a3942] border-2 border-transparent focus:border-blue-500/20 focus:bg-white dark:focus:bg-[#2a3942] rounded-[1.25rem] text-sm font-medium focus:ring-0 transition-all resize-none dark:text-white shadow-inner"></textarea>

                        <!-- Recording Overlay -->
                        <div id="recording-overlay" class="hidden absolute inset-0 bg-blue-600 rounded-[1.25rem] flex items-center px-5 gap-3 z-10 animate-in slide-in-from-bottom-2 duration-300">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 bg-white rounded-full animate-pulse shadow-[0_0_10px_white]"></div>
                                <span id="recording-timer" class="text-xs font-black text-white tracking-widest">00:00</span>
                            </div>
                            <div class="flex-1 text-[11px] font-bold text-blue-100 uppercase tracking-widest">Enregistrement...</div>
                            <button type="button" id="cancel-record-btn" class="p-1 text-white/70 hover:text-white transition-opacity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <button type="button" id="record-btn" class="w-12 h-12 flex items-center justify-center bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 rounded-2xl transition-all active:scale-90" title="Vocal">
                            <svg id="mic-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                            <svg id="send-vocal-icon" class="hidden w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                        
                        <button type="submit" id="submit-btn" class="hidden w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all active:scale-90 ml-2">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </form>
            </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/chat.js"></script>

