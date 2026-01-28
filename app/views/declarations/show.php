<?php
/**
 * Vue Détail de la Déclaration + Chat
 */
?>

<style>
    .chat-bg {
        background-color: #e5ddd5;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-blend-mode: overlay;
        background-attachment: fixed;
    }
    .dark .chat-bg {
        background-color: #0b141a;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-blend-mode: soft-light;
        opacity: 0.9;
    }
    .bubble-out {
        background-color: #dcf8c6;
        border-radius: 8px 0px 8px 8px;
    }
    .dark .bubble-out {
        background-color: #005c4b;
    }
    .bubble-in {
        background-color: #ffffff;
        border-radius: 0px 8px 8px 8px;
    }
    .dark .bubble-in {
        background-color: #202c33;
    }
    .bubble-tail-out::after {
        content: "";
        position: absolute;
        top: 0;
        right: -8px;
        width: 0;
        height: 0;
        border-top: 10px solid #dcf8c6;
        border-right: 10px solid transparent;
    }
    .dark .bubble-tail-out::after { border-top-color: #005c4b; }
    
    .bubble-tail-in::after {
        content: "";
        position: absolute;
        top: 0;
        left: -8px;
        width: 0;
        height: 0;
        border-top: 10px solid #ffffff;
        border-left: 10px solid transparent;
    }
    .dark .bubble-tail-in::after { border-top-color: #202c33; }

    .date-divider {
        background: rgba(225, 245, 254, 0.9);
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }
    .dark .date-divider {
        background: rgba(40, 50, 55, 0.9);
        color: #8696a0;
    }

    .bubble-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 2px;
        margin-top: -6px;
        padding-bottom: 2px;
    }

    /* Dropdown UI */
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        min-width: 180px;
        background-color: white;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 50;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 8px;
    }
    .dark .dropdown-content {
        background-color: #233138;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.4);
    }
    .dropdown-content button, .dropdown-content a {
        width: 100%;
        text-align: left;
        padding: 12px 16px;
        display: block;
        font-size: 14px;
        transition: background 0.2s;
    }
    .dropdown-content button:hover, .dropdown-content a:hover {
        background-color: #f5f5f5;
    }
    .dark .dropdown-content button:hover, .dark .dropdown-content a:hover {
        background-color: #182229;
    }
    .dropdown-content.active {
        display: block;
    }
</style>

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations de la Déclaration -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 dark:border-gray-800 pb-2">Détails du Paiement</h2>
                
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Montant</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white"><?= number_format($declaration['montant'], 0, ',', ' ') ?> FCFA</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Type</p>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
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
                        </p>
                    </div>

                    <?php if ($declaration['preuve_path']): ?>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-2">Preuve de paiement</p>
                        <a href="<?= BASE_URL ?>/<?= $declaration['preuve_path'] ?>" onclick="return window.openChatImage(this.href, event)" class="chat-image-link block group relative rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
                            <img src="<?= BASE_URL ?>/<?= $declaration['preuve_path'] ?>" alt="Preuve" class="w-full h-auto object-cover transition group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <span class="text-white text-xs font-bold uppercase">Voir en plein écran</span>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Admin -->
            <?php if (in_array($currentUser['role'], ['admin', 'comptable']) && $declaration['statut'] === 'EN_ATTENTE'): ?>
            <div class="bg-gray-900 dark:bg-white rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase text-center mb-2">Décision</h3>
                
                <form action="<?= BASE_URL ?>/declarations/validate" method="POST" onsubmit="return confirm('Confirmez-vous la validité de ce paiement ? Le solde du membre sera mis à jour immédiatement.');">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $declaration['id'] ?>">
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white dark:bg-green-600 dark:hover:bg-green-700 py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition transform hover:scale-[1.02]">
                        Valider le Paiement
                    </button>
                </form>

                <form action="<?= BASE_URL ?>/declarations/reject" method="POST" onsubmit="return confirm('Voulez-vous rejeter cette déclaration ?');">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $declaration['id'] ?>">
                    <button type="submit" class="w-full bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 py-3 rounded-2xl font-bold text-xs uppercase transition">
                        Rejeter
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Section Chat -->
        <div class="lg:col-span-2 flex flex-col h-[700px] bg-white dark:bg-[#0b141a] rounded-3xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden transition-colors">
            <!-- Header Chat -->
            <div class="flex items-center justify-between bg-white dark:bg-[#202c33] px-4 py-2 border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Discussion Déclaration</h3>
                        <span class="text-[10px] text-green-500 font-medium tracking-wider uppercase">En direct</span>
                    </div>
                </div>

                <div class="flex items-center gap-1 md:gap-4 relative">
                    <div id="search-container" class="hidden absolute right-full mr-2 items-center bg-gray-100 dark:bg-gray-800 rounded-full px-3 py-1 animate-in slide-in-from-right-2 duration-200 min-w-[200px] md:min-w-[300px]">
                        <input type="text" id="search-input" placeholder="Rechercher un message..." class="bg-transparent border-none text-xs md:text-sm focus:ring-0 w-full dark:text-white py-1">
                        <button id="close-search" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <button id="search-btn" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                    <div class="dropdown">
                        <button id="dropdown-toggle" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                        </button>
                        <div id="dropdown-menu" class="dropdown-content shadow-2xl border border-gray-100 dark:border-gray-800">
                        <button id="toggle-selection-mode" class="text-gray-700 dark:text-gray-300">Sélectionner des messages</button>
                        
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <form action="<?= BASE_URL ?>/declarations/clear-chat" method="POST" onsubmit="return confirm('Attention ! Cette action supprimera TOUS les messages et fichiers de cette discussion. Continuer ?')" class="block">
                                <input type="hidden" name="declaration_id" value="<?= $declaration['id'] ?>">
                                <button type="submit" class="w-full text-left text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    Effacer la discussion
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages List -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-4 chat-bg" id="chat-messages">
            <?php if (empty($messages)): ?>
                <div class="text-center py-20 bg-white/10 dark:bg-black/20 rounded-3xl m-4 backdrop-blur-sm">
                    <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.827-1.213L3 20l1.391-3.987A9 9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Commencez la discussion sur cette déclaration.</p>
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
                    <div class="flex justify-center my-6">
                        <span class="date-divider px-4 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider text-gray-600 shadow-sm">
                            <?= $displayDate ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="flex items-center gap-3 w-full group/row">
                        <?php if ($currentUser['role'] === 'admin' || $isMe): ?>
                            <input type="checkbox" value="<?= $m['id'] ?>" class="message-checkbox hidden w-5 h-5 rounded-full border-2 border-gray-300 text-red-500 focus:ring-red-500 transition-all cursor-pointer shrink-0 appearance-none checked:bg-red-500 checked:border-red-500 relative before:content-[''] before:absolute before:inset-0 before:flex before:items-center before:justify-center before:text-white before:text-[10px] before:font-black checked:before:content-['✓']">
                        <?php else: ?>
                            <div class="selection-placeholder hidden w-5 shrink-0"></div>
                        <?php endif; ?>

                        <div class="flex flex-col flex-1 <?= $isMe ? 'items-end' : 'items-start' ?>">
                        <div class="max-w-[90%] md:max-w-[75%] relative group">
                            <!-- Bubble -->
                            <div class="px-3 py-2 shadow-sm transition-colors relative 
                                <?= $isMe 
                                    ? 'bubble-out bubble-tail-out text-gray-900 dark:text-gray-100' 
                                    : 'bubble-in bubble-tail-in text-gray-800 dark:text-gray-100' ?>">
                                
                                <?php if (!$isMe): ?>
                                    <span class="block text-[11px] font-bold text-[#34b7f1] mb-1">
                                        <?= htmlspecialchars($m['sender_name'] ?? 'Admin') ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($m['message'])): ?>
                                    <p class="text-[14px] leading-relaxed pr-8"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
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
                                        <div class="vocal-player flex items-center gap-2 md:gap-3 p-1.5 md:p-2 bg-black/5 dark:bg-white/5 rounded-2xl min-w-0 md:min-w-[220px]" data-audio-src="<?= BASE_URL ?>/<?= $m['audio_path'] ?>">
                                            <button type="button" class="vocal-play-btn w-10 h-10 md:w-12 md:h-12 rounded-full bg-[#4caf50] text-white flex items-center justify-center shrink-0 hover:bg-[#43a047] transition shadow-sm active:scale-95">
                                                <svg class="play-icon w-5 h-5 md:w-6 md:h-6 fill-current ml-1 pointer-events-none" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                <svg class="pause-icon hidden w-5 h-5 md:w-6 md:h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
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

                                <div class="bubble-meta">
                                    <span class="text-[10px] opacity-50 uppercase">
                                        <?= date('H:i', strtotime($m['created_at'])) ?>
                                    </span>
                                    <?php if ($isMe): ?>
                                        <span class="text-[#34b7f1] flex">
                                            <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M18,7l-1.41-1.41-6.34,6.34 1.41,1.41L18,7zm4.24-4.24l-1.41-1.41L10.24,11.93l1.41,1.41L22.24,2.76zM7.16,16.5l-4.24-4.24-1.41,1.41 5.66,5.66 1.41-1.41L7.16,16.5z"/>
                                            </svg>
                                        </span>
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
        <div id="selection-action-bar" class="hidden px-3 md:px-6 py-2 md:py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 items-center justify-between animate-in slide-in-from-bottom duration-300">
            <div class="flex flex-col md:flex-row md:items-center gap-0 md:gap-3 leading-tight">
                <span class="text-xs md:text-sm font-bold text-gray-700 dark:text-gray-200 whitespace-nowrap"><span id="selected-count">0</span> sélectionné(s)</span>
                <button id="cancel-selection" class="text-[10px] md:text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white transition underline text-left">Annuler</button>
            </div>
            <form action="<?= BASE_URL ?>/declarations/delete-messages" method="POST" id="delete-form" onsubmit="return confirm('Supprimer les messages sélectionnés ?')">
                <div id="selected-ids-container"></div>
                <button type="submit" class="px-4 md:px-6 py-2 md:py-2.5 bg-red-500 hover:bg-red-600 text-white text-[11px] md:text-sm font-black uppercase tracking-wider md:tracking-widest rounded-xl md:rounded-2xl transition-all shadow-lg active:scale-95 flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Supprimer</span>
                </button>
            </form>
        </div>

            <!-- Chat Input -->
            <div class="px-2 py-3 bg-[#f0f2f5] dark:bg-[#202c33] transition-colors">
                <form id="chat-form" action="<?= BASE_URL ?>/declarations/message" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 max-w-5xl mx-auto px-2">
                    <input type="hidden" name="declaration_id" value="<?= $declaration['id'] ?>">
                    
                    <div class="flex items-center gap-1">
                        <button type="button" class="p-2 text-gray-500 hover:text-gray-900 dark:hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <label class="p-2 text-gray-500 hover:text-gray-900 dark:hover:text-white cursor-pointer transition" title="Joindre un média">
                            <input type="file" name="image" accept="image/*" class="hidden" onchange="this.form.submit()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </label>
                    </div>

                    <div class="relative flex-1 flex items-center">
                        <div id="input-container" class="flex-1">
                            <textarea name="message" id="chat-input" rows="1" placeholder="Tapez un message" required
                                class="w-full px-4 py-2 bg-white dark:bg-[#2a3942] border-none rounded-xl text-[15px] focus:ring-0 transition-all resize-none placeholder-gray-500 dark:text-white"></textarea>
                        </div>

                        <!-- Recording Overlay -->
                        <div id="recording-overlay" class="hidden absolute inset-0 bg-white dark:bg-[#2a3942] rounded-xl flex items-center px-4 gap-3 z-10 animate-in fade-in duration-200">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></div>
                                <span id="recording-timer" class="text-sm font-bold text-gray-700 dark:text-gray-300">00:00</span>
                            </div>
                            <div class="flex-1 text-sm text-gray-500 italic truncate">Enregistrement vocal...</div>
                            <button type="button" id="cancel-record-btn" class="p-1 text-gray-500 hover:text-red-500 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center text-[#00a884]">
                        <button type="button" id="record-btn" class="p-2.5 text-gray-500 hover:text-[#00a884] transition" title="Vocal">
                            <svg id="mic-icon" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                            <svg id="send-vocal-icon" class="hidden w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                        
                        <button type="submit" id="submit-btn" class="hidden p-2.5 hover:scale-110 transition active:scale-95">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize everything on load
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Scroll to bottom
        const chatContainer = document.getElementById('chat-messages');
        if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

        // 2. Vocal Players Initialization
        initVocalPlayers();

        // 3. Media Auto-submit
        const mediaInput = document.querySelector('input[name="image"]');
        if (mediaInput) {
            mediaInput.onchange = function() {
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
        const dateDividers = document.querySelectorAll('.flex.justify-center.my-6');

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
            toggleBtn.onclick = function() {
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
            cancelBtn.onclick = function() {
                checkboxes.forEach(cb => cb.checked = false);
                updateSelection();
            };
        }

        // 4. Input Bar & Voice Recording
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
            chatInput.addEventListener('input', function() {
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
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const formData = new FormData(chatForm);
                        formData.delete('message');
                        formData.append('audio', audioBlob, 'vocal.webm');
                        try {
                            const response = await fetch(chatForm.action, { method: 'POST', body: formData });
                            if (response.ok) window.location.reload();
                        } catch (err) { console.error("Upload failed:", err); }
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
                    recordingTimer.textContent = `${Math.floor(elapsed/60).toString().padStart(2,'0')}:${(elapsed%60).toString().padStart(2,'0')}`;
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

            const formatTime = (s) => isNaN(s) || s === Infinity ? "0:00" : `${Math.floor(s/60)}:${Math.floor(s%60).toString().padStart(2,'0')}`;
            const updateDur = () => {
                if (audio.duration && audio.duration !== Infinity) durationDisplay.textContent = formatTime(audio.duration);
                else if (audio.duration === Infinity) {
                    audio.currentTime = 1e101;
                    audio.ontimeupdate = function() { this.ontimeupdate = null; this.currentTime = 0; durationDisplay.textContent = formatTime(this.duration); };
                }
            };
            audio.addEventListener('canplay', updateDur);
            audio.addEventListener('timeupdate', () => {
                if (audio.duration && audio.duration !== Infinity) progressBar.style.width = `${(audio.currentTime/audio.duration)*100}%`;
                timeDisplay.textContent = formatTime(audio.currentTime);
            });
            audio.addEventListener('ended', () => { playIcon.classList.remove('hidden'); pauseIcon.classList.add('hidden'); progressBar.style.width = '0%'; timeDisplay.textContent = '0:00'; });
            playBtn.onclick = async (e) => {
                e.preventDefault(); e.stopPropagation();
                if (audio.paused) {
                    document.querySelectorAll('.hidden-audio').forEach(a => { if(a!==audio && !a.paused) { a.pause(); const p = a.closest('.vocal-player'); if(p) { p.querySelector('.play-icon').classList.remove('hidden'); p.querySelector('.pause-icon').classList.add('hidden'); } } });
                    await audio.play(); playIcon.classList.add('hidden'); pauseIcon.classList.remove('hidden');
                } else { audio.pause(); playIcon.classList.remove('hidden'); pauseIcon.classList.add('hidden'); }
            };
            progressContainer.onclick = (e) => { if (audio.duration && audio.duration !== Infinity) audio.currentTime = ((e.clientX - progressContainer.getBoundingClientRect().left) / progressContainer.offsetWidth) * audio.duration; };
        });
    }
</script>

