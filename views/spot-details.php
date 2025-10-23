<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary">Início</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><span class="text-primary"><?php echo htmlspecialchars($spot['title']); ?></span></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Images / Gallery -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="relative bg-gray-100 rounded-lg overflow-hidden">
                                <img id="mainImage" src="<?php echo !empty($images[0]) ? BASE_URL . '/' . $images[0]['image_url'] : ''; ?>"
                                     alt="<?php echo htmlspecialchars($spot['title']); ?>"
                                     class="w-full h-80 md:h-96 object-cover"
                                     onerror="handleImageError(this)">
                                <div id="mainPlaceholder" class="w-full h-80 md:h-96 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-parking text-6xl text-gray-400"></i>
                                </div>
                            </div>

                            <?php if (!empty($images) && count($images) > 1): ?>
                            <div class="mt-3 flex gap-2 overflow-x-auto">
                                <?php foreach($images as $index => $image): ?>
                                    <button type="button" class="thumb flex-none w-20 h-20 rounded-lg overflow-hidden border-2 <?php echo $index === 0 ? 'border-primary' : 'border-transparent'; ?>"
                                            data-src="<?php echo BASE_URL . '/' . $image['image_url']; ?>"
                                            aria-label="Abrir imagem <?php echo $index + 1; ?>">
                                        <img src="<?php echo BASE_URL . '/' . $image['image_url']; ?>"
                                             alt="<?php echo htmlspecialchars($spot['title']) . ' - ' . ($index + 1); ?>"
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.closest('button').classList.add('bg-gray-200')">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Spot Details -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h1 class="text-3xl font-bold text-primary mb-4"><?php echo htmlspecialchars($spot['title']); ?></h1>

                    <div class="flex items-center mb-4">
                        <div class="flex items-center space-x-1">
                            <?php
                            $rating = $spot['avg_rating'] ?? 0;
                            for($i = 1; $i <= 5; $i++):
                            ?>
                                <i class="fas fa-star <?php echo $i <= $rating ? 'text-secondary' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="ml-2 text-gray-600"><?php echo number_format($rating, 1); ?> (<?php echo $spot['review_count']; ?>)</span>
                    </div>

                    <div class="flex items-center text-gray-600 mb-6">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span><?php echo htmlspecialchars($spot['address']); ?>, <?php echo htmlspecialchars($spot['city']); ?> - <?php echo htmlspecialchars($spot['state']); ?></span>
                    </div>

                    <?php if (!empty($spot['description'])): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-primary mb-2">Descrição</h3>
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($spot['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Características</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($spot['is_covered']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-shield-alt mr-2 text-green-600"></i>Coberta</span>
                            <?php endif; ?>
                            <?php if ($spot['has_security']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-user-shield mr-2 text-green-600"></i>Segurança 24h</span>
                            <?php endif; ?>
                            <?php if ($spot['has_camera']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-video mr-2 text-green-600"></i>Câmeras</span>
                            <?php endif; ?>
                            <?php if ($spot['has_lighting']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-lightbulb mr-2 text-green-600"></i>Iluminação</span>
                            <?php endif; ?>
                            <?php if ($spot['has_electric_charging']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-charging-station mr-2 text-green-600"></i>Carregador</span>
                            <?php endif; ?>
                            <?php if ($spot['has_smart_lock']): ?>
                                <span class="px-3 py-1 bg-gray-100 text-sm rounded-lg flex items-center"><i class="fas fa-lock mr-2 text-green-600"></i>Acesso Inteligente</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Owner Info -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Proprietário</h3>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center mr-4">
                                <span class="text-primary font-bold text-lg">
                                    <?php echo strtoupper(substr($spot['owner_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-primary"><?php echo htmlspecialchars($spot['owner_name']); ?></p>
                                <?php if ($spot['owner_verified']): ?>
                                    <p class="text-sm text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>Verificado
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($spot['owner_contact'])): ?>
                                <div class="ml-4">
                                    <a href="mailto:<?php echo htmlspecialchars($spot['owner_contact']); ?>" class="inline-flex items-center px-3 py-2 bg-primary text-white rounded-lg hover:opacity-90">
                                        <i class="fas fa-envelope mr-2"></i>Contatar
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <?php if (!empty($reviews)): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold text-primary mb-4">Avaliações</h3>

                    <?php foreach($reviews as $review): ?>
                        <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-bold text-gray-600">
                                            <?php echo strtoupper(substr($review['reviewer_name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                    <span class="font-semibold text-primary"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-secondary' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="text-gray-700"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-500 mt-2">
                                <?php echo formatDateTime($review['created_at']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Booking Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-primary mb-2">
                            R$ <?php echo number_format($spot['price_daily'], 2, ',', '.'); ?>
                        </div>
                        <span class="text-gray-600">por dia</span>
                    </div>

                    <?php if (AuthController::isLoggedIn() && $spot['owner_id'] != $_SESSION['user_id']): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>/booking/create" class="space-y-4" id="bookingForm">
                            <?php echo csrfTokenInput(); ?>
                            <input type="hidden" name="spot_id" value="<?php echo $spot['id']; ?>">

                            <div>
                                <label class="block text-sm font-semibold text-primary mb-2">Data de Início</label>
                                <input type="date" name="start_date" id="startDate"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-primary mb-2">Data de Fim</label>
                                <input type="date" name="end_date" id="endDate"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-primary mb-2">Método de Pagamento</label>
                                <select name="payment_method" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                                    <option value="pix">PIX</option>
                                    <option value="credit_card">Cartão de Crédito</option>
                                    <option value="boleto">Boleto</option>
                                </select>
                            </div>

                            <div class="border-t pt-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>Preço diário:</span>
                                    <span>R$ <?php echo number_format($spot['price_daily'], 2, ',', '.'); ?></span>
                                </div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span>Taxa de serviço (10%):</span>
                                    <span id="serviceFee">R$ 0,00</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg border-t pt-2">
                                    <span>Total:</span>
                                    <span id="totalPrice">R$ 0,00</span>
                                </div>
                            </div>

                            <button type="submit" id="reserveBtn"
                                    class="w-full bg-accent text-primary py-3 rounded-lg font-semibold hover:bg-accent-dark transition disabled:opacity-50"
                                    disabled>
                                <i class="fas fa-calendar-check mr-2"></i>Reservar Agora
                            </button>
                        </form>
                    <?php elseif (AuthController::isLoggedIn() && $spot['owner_id'] == $_SESSION['user_id']): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Esta é sua própria vaga</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-600 mb-4">Faça login para reservar esta vaga</p>
                            <a href="<?php echo BASE_URL; ?>/login"
                               class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                                Entrar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* Image handling: show placeholder when image fails and allow thumbnail swapping */
function handleImageError(img) {
    const placeholder = document.getElementById('mainPlaceholder');
    if (img) img.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
}

function setMainImage(src) {
    const main = document.getElementById('mainImage');
    const placeholder = document.getElementById('mainPlaceholder');
    if (!main) return;
    if (!src) {
        main.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        return;
    }
    main.src = src;
    main.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
}

/* Initialize gallery thumbnails */
document.addEventListener('DOMContentLoaded', function() {
    const thumbs = document.querySelectorAll('.thumb');
    thumbs.forEach(btn => {
        btn.addEventListener('click', function() {
            const src = this.getAttribute('data-src');
            setMainImage(src);

            // mark active thumbnail
            thumbs.forEach(t => t.classList.remove('border-primary'));
            this.classList.add('border-primary');
        });
    });

    // If main image missing, show placeholder
    const main = document.getElementById('mainImage');
    const mainPlaceholder = document.getElementById('mainPlaceholder');
    if (main && mainPlaceholder) {
        main.addEventListener('load', () => {
            mainPlaceholder.style.display = 'none';
            main.style.display = 'block';
        });
        main.addEventListener('error', () => handleImageError(main));
        if (!main.src) {
            main.style.display = 'none';
            mainPlaceholder.style.display = 'flex';
        } else {
            // try to show placeholder briefly until load
            mainPlaceholder.style.display = 'none';
        }
    }

    // Booking form wiring
    const start = document.getElementById('startDate');
    const end = document.getElementById('endDate');
    if (start) start.addEventListener('change', calculateTotal);
    if (end) end.addEventListener('change', calculateTotal);

    // Initial calculate to update totals if dates prefilled
    calculateTotal();
});

/* Price helpers */
function formatCurrencyBR(value) {
    return value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

/* Calculate total price with validation */
function calculateTotal() {
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const serviceFeeEl = document.getElementById('serviceFee');
    const totalPriceEl = document.getElementById('totalPrice');
    const reserveBtn = document.getElementById('reserveBtn');

    if (!startInput || !endInput) return;

    const startVal = startInput.value;
    const endVal = endInput.value;
    const dailyPrice = parseFloat(<?php echo json_encode($spot['price_daily']); ?>) || 0;

    // disable by default
    if (reserveBtn) reserveBtn.disabled = true;

    if (!startVal || !endVal) {
        if (serviceFeeEl) serviceFeeEl.textContent = 'R$ 0,00';
        if (totalPriceEl) totalPriceEl.textContent = 'R$ 0,00';
        return;
    }

    const startDate = new Date(startVal + 'T00:00:00');
    const endDate = new Date(endVal + 'T00:00:00');

    if (isNaN(startDate) || isNaN(endDate) || endDate < startDate) {
        // invalid date range
        if (serviceFeeEl) serviceFeeEl.textContent = 'R$ 0,00';
        if (totalPriceEl) totalPriceEl.textContent = 'R$ 0,00';
        return;
    }

    // Compute inclusive days (start <= end)
    const msPerDay = 1000 * 60 * 60 * 24;
    const days = Math.floor((endDate - startDate) / msPerDay) + 1; // inclusive

    if (days <= 0) {
        if (serviceFeeEl) serviceFeeEl.textContent = 'R$ 0,00';
        if (totalPriceEl) totalPriceEl.textContent = 'R$ 0,00';
        return;
    }

    const total = dailyPrice * days;
    const serviceFee = total * 0.10;
    const final = total + serviceFee;

    if (serviceFeeEl) serviceFeeEl.textContent = 'R$ ' + formatCurrencyBR(serviceFee);
    if (totalPriceEl) totalPriceEl.textContent = 'R$ ' + formatCurrencyBR(final);

    if (reserveBtn) reserveBtn.disabled = false;
}
</script>







