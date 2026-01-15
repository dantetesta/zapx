<?php 
$pageTitle = 'Meu Perfil - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-circle mr-2 gradient-text"></i>
                Meu Perfil
            </h1>
            <p class="mt-2 text-gray-600">Gerencie suas informações pessoais e configurações</p>
        </div>

        <?php if (isset($success)): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($errors) && !empty($errors)): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mr-2 mt-0.5"></i>
                <div class="flex-1">
                    <?php foreach ($errors as $error): ?>
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo APP_URL; ?>/users/profile" class="space-y-6">
            <!-- Informações Pessoais -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Informações Pessoais
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    
                </div>
                
                <!-- DDI e Timezone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- DDI Padrão -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-globe mr-1"></i>
                            DDI Padrão (Código do País)
                        </label>
                        <input type="number" 
                               name="default_country_code" 
                               value="<?php echo htmlspecialchars($userData['default_country_code'] ?? '55'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Ex: 55"
                               min="1"
                               max="9999"
                               step="1"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               required>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Digite apenas números
                        </p>
                        <details class="mt-2">
                            <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-700">
                                <i class="fas fa-list mr-1"></i>
                                Ver DDIs comuns
                            </summary>
                            <div class="mt-2 p-3 bg-gray-50 rounded-lg text-xs max-h-48 overflow-y-auto">
                                <div class="grid grid-cols-2 gap-2">
                                    <div><strong>55</strong> - Brasil</div>
                                    <div><strong>1</strong> - EUA/Canadá</div>
                                    <div><strong>351</strong> - Portugal</div>
                                    <div><strong>54</strong> - Argentina</div>
                                    <div><strong>52</strong> - México</div>
                                    <div><strong>34</strong> - Espanha</div>
                                    <div><strong>44</strong> - Reino Unido</div>
                                    <div><strong>49</strong> - Alemanha</div>
                                    <div><strong>33</strong> - França</div>
                                    <div><strong>39</strong> - Itália</div>
                                    <div><strong>81</strong> - Japão</div>
                                    <div><strong>86</strong> - China</div>
                                    <div><strong>91</strong> - Índia</div>
                                    <div><strong>61</strong> - Austrália</div>
                                    <div><strong>93</strong> - Afeganistão</div>
                                    <div><strong>27</strong> - África do Sul</div>
                                </div>
                            </div>
                        </details>
                    </div>
                    
                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-1"></i>
                            Fuso Horário
                        </label>
                        <input type="text" 
                               name="timezone" 
                               list="timezoneList"
                               value="<?php echo htmlspecialchars($userData['timezone'] ?? 'America/Sao_Paulo'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Ex: America/Sao_Paulo"
                               required>
                        <datalist id="timezoneList">
                            <?php
                            $timezones = [
                                'America/Sao_Paulo' => '🇧🇷 Brasil - São Paulo (UTC-3)',
                                'America/Manaus' => '🇧🇷 Brasil - Manaus (UTC-4)',
                                'America/Rio_Branco' => '🇧🇷 Brasil - Acre (UTC-5)',
                                'America/Noronha' => '🇧🇷 Brasil - Fernando de Noronha (UTC-2)',
                                'America/New_York' => '🇺🇸 EUA - Nova York (UTC-5)',
                                'America/Chicago' => '🇺🇸 EUA - Chicago (UTC-6)',
                                'America/Denver' => '🇺🇸 EUA - Denver (UTC-7)',
                                'America/Los_Angeles' => '🇺🇸 EUA - Los Angeles (UTC-8)',
                                'America/Mexico_City' => '🇲🇽 México - Cidade do México (UTC-6)',
                                'America/Argentina/Buenos_Aires' => '🇦🇷 Argentina - Buenos Aires (UTC-3)',
                                'America/Santiago' => '🇨🇱 Chile - Santiago (UTC-3)',
                                'America/Bogota' => '🇨🇴 Colômbia - Bogotá (UTC-5)',
                                'America/Lima' => '🇵🇪 Peru - Lima (UTC-5)',
                                'Europe/Lisbon' => '🇵🇹 Portugal - Lisboa (UTC+0)',
                                'Europe/Madrid' => '🇪🇸 Espanha - Madrid (UTC+1)',
                                'Europe/London' => '🇬🇧 Reino Unido - Londres (UTC+0)',
                                'Europe/Paris' => '🇫🇷 França - Paris (UTC+1)',
                                'Europe/Berlin' => '🇩🇪 Alemanha - Berlim (UTC+1)',
                                'Europe/Rome' => '🇮🇹 Itália - Roma (UTC+1)',
                                'Asia/Tokyo' => '🇯🇵 Japão - Tóquio (UTC+9)',
                                'Asia/Shanghai' => '🇨🇳 China - Xangai (UTC+8)',
                                'Asia/Dubai' => '🇦🇪 Emirados Árabes - Dubai (UTC+4)',
                                'Australia/Sydney' => '🇦🇺 Austrália - Sydney (UTC+10)',
                                'Pacific/Auckland' => '🇳🇿 Nova Zelândia - Auckland (UTC+12)',
                            ];
                            
                            foreach ($timezones as $value => $label) {
                                echo "<option value=\"$value\">$label</option>";
                            }
                            ?>
                        </datalist>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Digite ou selecione da lista. Formato: Continente/Cidade
                        </p>
                        <a href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center text-xs text-blue-600 hover:text-blue-700 mt-1">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Ver lista completa de timezones
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alterar Senha -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-lock mr-2 text-yellow-600"></i>
                    Alterar Senha
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Senha Atual</label>
                        <input type="password" name="current_password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Digite sua senha atual">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                            <input type="password" name="new_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Mínimo 6 caracteres">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha</label>
                            <input type="password" name="confirm_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="Repita a nova senha">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão Salvar -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>


<?php include 'views/layouts/footer.php'; ?>
