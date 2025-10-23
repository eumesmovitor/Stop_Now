
<?php $pageTitle = 'Editar Vaga - StopNow'; ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-primary mb-4">Editar Vaga</h1>

            <form method="POST" action="<?php echo BASE_URL; ?>/spot/update" enctype="multipart/form-data" id="editSpotForm" class="space-y-4">
                <?php echo csrfTokenInput(); ?>
                <input type="hidden" name="spot_id" value="<?php echo htmlspecialchars($spot['id']); ?>">

                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">Título</label>
                    <input name="title" required value="<?php echo htmlspecialchars($spot['title']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">Descrição</label>
                    <textarea name="description" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg"><?php echo htmlspecialchars($spot['description']); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Preço diário (R$)</label>
                        <input name="price_daily" type="number" step="0.01" required value="<?php echo htmlspecialchars($spot['price_daily']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Preço semanal (R$)</label>
                        <input name="price_weekly" type="number" step="0.01" value="<?php echo htmlspecialchars($spot['price_weekly'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Preço mensal (R$)</label>
                        <input name="price_monthly" type="number" step="0.01" value="<?php echo htmlspecialchars($spot['price_monthly'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Preço anual (R$)</label>
                        <input name="price_annual" type="number" step="0.01" value="<?php echo htmlspecialchars($spot['price_annual'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Cidade</label>
                        <input name="city" value="<?php echo htmlspecialchars($spot['city']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Estado</label>
                        <input name="state" value="<?php echo htmlspecialchars($spot['state']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Endereço</label>
                        <input name="address" value="<?php echo htmlspecialchars($spot['address']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">CEP</label>
                        <input name="zip_code" value="<?php echo htmlspecialchars($spot['zip_code']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Tipo de Vaga</label>
                        <select name="spot_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="uncovered" <?php echo $spot['spot_type'] === 'uncovered' ? 'selected' : ''; ?>>Descoberta</option>
                            <option value="covered" <?php echo $spot['spot_type'] === 'covered' ? 'selected' : ''; ?>>Coberta</option>
                            <option value="garage" <?php echo $spot['spot_type'] === 'garage' ? 'selected' : ''; ?>>Garagem</option>
                            <option value="street" <?php echo $spot['spot_type'] === 'street' ? 'selected' : ''; ?>>Rua</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Altura Máxima (m)</label>
                        <input name="max_height" type="number" step="0.1" value="<?php echo htmlspecialchars($spot['max_height'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Largura Máxima (m)</label>
                        <input name="max_width" type="number" step="0.1" value="<?php echo htmlspecialchars($spot['max_width'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Tipo de Fechadura Inteligente</label>
                        <select name="smart_lock_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">Selecione...</option>
                            <option value="app" <?php echo $spot['smart_lock_type'] === 'app' ? 'selected' : ''; ?>>App Mobile</option>
                            <option value="code" <?php echo $spot['smart_lock_type'] === 'code' ? 'selected' : ''; ?>>Código</option>
                            <option value="card" <?php echo $spot['smart_lock_type'] === 'card' ? 'selected' : ''; ?>>Cartão</option>
                            <option value="remote" <?php echo $spot['smart_lock_type'] === 'remote' ? 'selected' : ''; ?>>Controle Remoto</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-primary mb-2">Características</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_covered" value="1" <?php echo $spot['is_covered'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Coberta</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="has_security" value="1" <?php echo $spot['has_security'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Segurança</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="has_camera" value="1" <?php echo $spot['has_camera'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Câmeras</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="has_lighting" value="1" <?php echo $spot['has_lighting'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Iluminação</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="has_electric_charging" value="1" <?php echo $spot['has_electric_charging'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Carregamento Elétrico</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="has_smart_lock" value="1" <?php echo $spot['has_smart_lock'] ? 'checked' : ''; ?> />
                            <span class="text-sm">Acesso Inteligente</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">Imagens (envie novas para adicionar)</label>
                    <input type="file" name="images[]" accept="image/*" multiple class="w-full" />
                    <?php if (!empty($images)): ?>
                        <div class="mt-3 grid grid-cols-4 gap-2">
                            <?php foreach($images as $img): ?>
                                <label class="relative block">
                                    <img src="<?php echo BASE_URL . '/' . $img['image_url']; ?>" alt="" class="w-full h-24 object-cover rounded border" onerror="this.style.display='none'">
                                    <input type="checkbox" name="remove_images[]" value="<?php echo $img['id']; ?>" class="absolute top-1 right-1 bg-white p-1 rounded" />
                                    <span class="absolute top-1 left-1 bg-white text-xs px-1 rounded">Remover</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="text-sm text-gray-600 hover:underline">&larr; Voltar</a>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo BASE_URL; ?>/spot/<?php echo $spot['id']; ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Ver vaga</a>
                        <button type="submit" class="bg-accent text-primary px-6 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">Salvar alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editSpotForm')?.addEventListener('submit', function(){
    // simple UX: disable submit to avoid double posts
    const btn = this.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;
});
</script>