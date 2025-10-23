
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 bg-secondary rounded-lg flex items-center justify-center">
                <i class="fas fa-parking text-primary text-2xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-primary">
                Crie sua conta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ou
                <a href="<?php echo BASE_URL; ?>/login" class="font-medium text-accent hover:text-accent-dark">
                    entre com sua conta existente
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="<?php echo BASE_URL; ?>/register">
            <?php echo csrfTokenInput(); ?>
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-primary">Nome Completo *</label>
                    <input id="name" name="name" type="text" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="Seu nome completo">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-primary">Email *</label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="seu@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-primary">Senha *</label>
                    <input id="password" name="password" type="password" required minlength="6"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="Mínimo 6 caracteres">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-primary">Confirmar Senha *</label>
                    <input id="confirm_password" name="confirm_password" type="password" required minlength="6"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="Confirme sua senha">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-primary">Telefone</label>
                    <input id="phone" name="phone" type="tel"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="(11) 99999-9999">
                </div>
                
                <div>
                    <label for="cpf" class="block text-sm font-medium text-primary">CPF</label>
                    <input id="cpf" name="cpf" type="text"
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-primary rounded-md focus:outline-none focus:ring-accent focus:border-accent focus:z-10 sm:text-sm"
                           placeholder="000.000.000-00">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required
                       class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900">
                    Eu aceito os <a href="#" class="text-accent hover:text-accent-dark">Termos de Uso</a> e <a href="#" class="text-accent hover:text-accent-dark">Política de Privacidade</a>
                </label>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-primary bg-accent hover:bg-accent-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-user-plus text-primary" aria-hidden="true"></i>
                    </span>
                    Criar Conta
                </button>
            </div>
        </form>
    </div>
</div>





