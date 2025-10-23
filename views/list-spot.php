<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-primary mb-2">Anunciar Nova Vaga</h1>
                <p class="text-gray-600">Preencha as informações abaixo para cadastrar sua vaga</p>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>/list-spot" enctype="multipart/form-data" class="space-y-8">
                <?php echo csrfTokenInput(); ?>
                <!-- Basic Information -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-accent"></i>
                        Informações Básicas
                    </h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Título do Anúncio *</label>
                            <input type="text" name="title" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="Ex: Vaga coberta no centro">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Descrição</label>
                            <textarea name="description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                      placeholder="Descreva sua vaga, características especiais, instruções de acesso, etc."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-accent"></i>
                        Localização
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">Endereço Completo *</label>
                            <input type="text" name="address" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="Rua, número, complemento">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Cidade *</label>
                            <input type="text" name="city" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Estado *</label>
                            <select name="state" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="SP">São Paulo</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <!-- Add more states -->
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">CEP *</label>
                            <input type="text" name="zip_code" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="00000-000">
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-dollar-sign mr-2 text-accent"></i>
                        Preços
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Diária (R$) *</label>
                            <input type="number" name="price_daily" step="0.01" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Semanal (R$)</label>
                            <input type="number" name="price_weekly" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="0.00">
                            <p class="text-xs text-gray-500 mt-1">Opcional - desconto para 7 dias</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Mensal (R$)</label>
                            <input type="number" name="price_monthly" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="0.00">
                            <p class="text-xs text-gray-500 mt-1">Opcional - desconto para 30 dias</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Anual (R$)</label>
                            <input type="number" name="price_annual" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                   placeholder="0.00">
                            <p class="text-xs text-gray-500 mt-1">Opcional - desconto para 365 dias</p>
                        </div>
                    </div>
                </div>

                <!-- Spot Type -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-car mr-2 text-accent"></i>
                        Tipo de Vaga
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Tipo *</label>
                            <select name="spot_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                                <option value="covered">Coberta</option>
                                <option value="uncovered">Descoberta</option>
                                <option value="garage">Garagem</option>
                                <option value="street">Rua</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Altura Máx (m)</label>
                                <input type="number" name="max_height" step="0.01"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                       placeholder="2.00">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Largura Máx (m)</label>
                                <input type="number" name="max_width" step="0.01"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                       placeholder="2.50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-camera mr-2 text-accent"></i>
                        Fotos da Vaga
                    </h2>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Adicionar Fotos *</label>
                        <input type="file" name="images[]" multiple accept="image/*" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Selecione até 5 fotos (JPG, PNG, máx 5MB cada)</p>
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                        <i class="fas fa-star mr-2 text-accent"></i>
                        Características
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="is_covered" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-shield-alt mr-2 text-accent"></i>Coberta</span>
                        </label>
                        
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="has_security" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-user-shield mr-2 text-accent"></i>Segurança 24h</span>
                        </label>
                        
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="has_camera" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-video mr-2 text-accent"></i>Câmeras</span>
                        </label>
                        
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="has_lighting" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-lightbulb mr-2 text-accent"></i>Iluminação</span>
                        </label>
                        
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="has_electric_charging" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-charging-station mr-2 text-accent"></i>Carregador Elétrico</span>
                        </label>
                        
                        <label class="flex items-center space-x-3 p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="has_smart_lock" id="smart_lock_check" class="w-5 h-5 text-accent rounded focus:ring-accent">
                            <span class="text-gray-700"><i class="fas fa-lock mr-2 text-accent"></i>Fechadura Inteligente</span>
                        </label>
                    </div>
                    
                    <div id="smart_lock_options" class="mt-4 hidden">
                        <label class="block text-gray-700 font-semibold mb-2">Tipo de Fechadura</label>
                        <select name="smart_lock_type"
                                class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                            <option value="">Selecione...</option>
                            <option value="yale">Yale Smart Lock</option>
                            <option value="august">August Smart Lock</option>
                            <option value="schlage">Schlage Encode</option>
                            <option value="other">Outro</option>
                        </select>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="<?php echo BASE_URL; ?>/dashboard" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-accent hover:bg-accent-dark text-primary font-semibold rounded-lg transition">
                        <i class="fas fa-check mr-2"></i>Publicar Vaga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('smart_lock_check').addEventListener('change', function() {
    document.getElementById('smart_lock_options').classList.toggle('hidden', !this.checked);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>