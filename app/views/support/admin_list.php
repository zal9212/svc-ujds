<?php
/**
 * Vue Liste Admin des conversations Support
 */
?>

<div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Conversations Support</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Espace de discussion générale avec les membres.</p>
</div>

<div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
    <?php if (empty($conversations)): ?>
        <div class="p-16 text-center text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.827-1.213L3 20l1.391-3.987A9 9 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            <p class="text-lg font-medium">Aucune conversation en cours</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-950 transition-colors">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Membre</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Dernier Message</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 transition-colors">
                    <?php foreach ($conversations as $c): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-gray-900 dark:text-white group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-all">
                                        <?= strtoupper(substr($c['designation'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white transition-colors"><?= htmlspecialchars($c['designation']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= htmlspecialchars($c['code']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-600 dark:text-gray-400 italic max-w-xs truncate">
                                    <span class="font-bold text-gray-400"><?= htmlspecialchars($c['last_sender']) ?>:</span> 
                                    <?= htmlspecialchars($c['message'] ?: '(Image)') ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="<?= BASE_URL ?>/support/view?membre_id=<?= $c['membre_id'] ?>" class="inline-flex items-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-xl text-xs font-bold transition hover:bg-black dark:hover:bg-gray-200 shadow-md">
                                    Ouvrir le chat
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
