<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAPX - Disparo em Massa WhatsApp</title>
    <meta name="description" content="Dispare mensagens em massa no WhatsApp com fotos, vídeos, áudios e documentos. Gerencie contatos com tags. Planos a partir de R$ 30/mês.">
    <meta name="keywords" content="WhatsApp Business, disparo em massa, envio em massa, mensagens whatsapp, ZAPX">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .whatsapp-green { background-color: #25D366; }
        .whatsapp-green:hover { background-color: #128C7E; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-pulse-slow { animation: pulse 3s infinite; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header/Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-bolt text-blue-600 mr-2"></i>
                            ZAPX
                        </h1>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="#hero" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Início</a>
                        <a href="#features" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Funcionalidades</a>
                        <a href="#pricing" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Preços</a>
                        <a href="#comprar-sistema" class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:from-yellow-600 hover:to-orange-600 transition shadow-lg animate-pulse-slow">
                            <i class="fas fa-shopping-cart mr-1"></i>
                            Comprar Sistema
                        </a>
                        <a href="<?php echo APP_URL; ?>/auth/login" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                            Entrar
                        </a>
                    </div>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="#hero" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Início</a>
                <a href="#features" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Funcionalidades</a>
                <a href="#pricing" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Preços</a>
                <a href="#comprar-sistema" class="block px-3 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg text-center font-bold">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Comprar Sistema
                </a>
                <a href="<?php echo APP_URL; ?>/auth/login" class="block px-3 py-2 bg-blue-600 text-white rounded-lg text-center">Entrar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="gradient-bg pt-20 pb-16 lg:pt-32 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <h1 class="text-4xl font-bold text-white sm:text-5xl lg:text-6xl">
                        Automatize seu
                        <span class="text-green-300">WhatsApp Business</span>
                        com ZAPX
                    </h1>
                    <p class="mt-6 text-xl text-blue-100 sm:max-w-3xl">
                        Dispare mensagens em massa com fotos, vídeos, áudios e documentos. 
                        Gerencie seus contatos com tags e alcance milhares de pessoas de forma rápida e eficiente.
                    </p>
                    <div class="mt-8 sm:max-w-lg sm:mx-auto sm:text-center lg:text-left lg:mx-0">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="#pricing" class="whatsapp-green text-white px-8 py-4 rounded-lg text-lg font-semibold hover:shadow-lg transition-all duration-300 text-center">
                                <i class="fab fa-whatsapp mr-2"></i>
                                Começar Agora
                            </a>
                            <a href="#features" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:shadow-lg transition-all duration-300 text-center">
                                Ver Funcionalidades
                            </a>
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="mt-12 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <div class="text-3xl font-bold text-white">99%</div>
                            <div class="text-blue-200 text-sm">Taxa de Entrega</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-white">15K</div>
                            <div class="text-blue-200 text-sm">Mensagens/Mês</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-white">∞</div>
                            <div class="text-blue-200 text-sm">Contatos</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-12 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6 lg:flex lg:items-center">
                    <div class="relative mx-auto w-full rounded-lg shadow-lg lg:max-w-md">
                        <div class="relative block w-full bg-white rounded-lg overflow-hidden animate-float">
                            <div class="bg-green-500 p-4 text-white text-center">
                                <i class="fab fa-whatsapp text-3xl mb-2"></i>
                                <h3 class="font-semibold">WhatsApp Business</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                                    <span class="text-sm text-gray-600">Mensagem enviada</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse-slow"></div>
                                    <span class="text-sm text-gray-600">Resposta automática</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse-slow"></div>
                                    <span class="text-sm text-gray-600">Lead qualificado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl lg:text-5xl">
                    Funcionalidades Poderosas
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    Tudo que você precisa para disparar mensagens em massa no WhatsApp
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-paper-plane text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Disparo em Massa</h3>
                    <p class="text-gray-600">
                        Envie mensagens para todos os contatos ou apenas para tags específicas. 
                        Disparo individual também disponível.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-images text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Envio de Mídias</h3>
                    <p class="text-gray-600">
                        Envie fotos, vídeos, áudios e documentos. 
                        Mensagens de texto também disponíveis.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-tags text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Classificação por Tags</h3>
                    <p class="text-gray-600">
                        Organize seus contatos com tags personalizadas. 
                        Segmente e envie mensagens direcionadas.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-users text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Contatos Ilimitados</h3>
                    <p class="text-gray-600">
                        Adicione quantos contatos quiser. 
                        Sem limite de armazenamento na sua lista.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-file-csv text-yellow-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Importar/Exportar CSV</h3>
                    <p class="text-gray-600">
                        Importe sua lista de contatos via CSV. 
                        Exporte seus dados quando precisar.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">100% Seguro</h3>
                    <p class="text-gray-600">
                        Plataforma segura e confiável. Seus dados protegidos 
                        com criptografia de ponta a ponta.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing/CTA Section -->
    <section id="pricing" class="py-16 lg:py-24 bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                    Escolha Seu Plano
                </h2>
                <p class="mt-4 text-xl text-gray-300 max-w-3xl mx-auto">
                    Planos flexíveis para atender sua necessidade de disparo em massa
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Plano Start -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Plano Start</h3>
                        <p class="text-blue-100 mt-2">Ideal para começar</p>
                    </div>
                    
                    <div class="px-6 py-8">
                        <div class="text-center mb-6">
                            <div class="text-4xl font-bold text-gray-900">R$ 30</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Até 1.000 mensagens/mês</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Contatos ilimitados</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Texto, fotos e vídeos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Documentos e áudios</span>
                            </li>
                        </ul>
                        
                        <div class="text-center">
                            <a href="https://wa.me/5519998021956?text=Olá! Tenho interesse no ZAPX - Plano Start (R$ 30/mês). Podem me ajudar?" 
                               target="_blank"
                               class="whatsapp-green text-white px-6 py-3 rounded-lg text-base font-semibold hover:shadow-lg transition-all duration-300 inline-block w-full text-center">
                                <i class="fab fa-whatsapp mr-2"></i>
                                Contratar via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plano Personal - DESTAQUE -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-transform duration-300 border-4 border-green-500 relative">
                    <div class="absolute top-0 right-0 bg-green-500 text-white px-4 py-1 text-sm font-bold rounded-bl-lg">
                        POPULAR
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Plano Personal</h3>
                        <p class="text-green-100 mt-2">Mais vendido</p>
                    </div>
                    
                    <div class="px-6 py-8">
                        <div class="text-center mb-6">
                            <div class="text-4xl font-bold text-gray-900">R$ 50</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Até 5.000 mensagens/mês</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Contatos ilimitados</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Texto, fotos e vídeos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Documentos e áudios</span>
                            </li>
                        </ul>
                        
                        <div class="text-center">
                            <a href="https://wa.me/5519998021956?text=Olá! Tenho interesse no ZAPX - Plano Personal (R$ 50/mês). Podem me ajudar?" 
                               target="_blank"
                               class="whatsapp-green text-white px-6 py-3 rounded-lg text-base font-semibold hover:shadow-lg transition-all duration-300 inline-block w-full text-center">
                                <i class="fab fa-whatsapp mr-2"></i>
                                Contratar via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plano Business -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Plano Business</h3>
                        <p class="text-purple-100 mt-2">Para empresas</p>
                    </div>
                    
                    <div class="px-6 py-8">
                        <div class="text-center mb-6">
                            <div class="text-4xl font-bold text-gray-900">R$ 100</div>
                            <div class="text-gray-500">/mês</div>
                        </div>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Até 15.000 mensagens/mês</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Contatos ilimitados</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Texto, fotos e vídeos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Documentos e áudios</span>
                            </li>
                        </ul>
                        
                        <div class="text-center">
                            <a href="https://wa.me/5519998021956?text=Olá! Tenho interesse no ZAPX - Plano Business (R$ 100/mês). Podem me ajudar?" 
                               target="_blank"
                               class="whatsapp-green text-white px-6 py-3 rounded-lg text-base font-semibold hover:shadow-lg transition-all duration-300 inline-block w-full text-center">
                                <i class="fab fa-whatsapp mr-2"></i>
                                Contratar via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- White Label / Revenda -->
            <div id="comprar-sistema" class="mt-20 scroll-mt-20">
                <div class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 rounded-2xl p-8 md:p-12 max-w-4xl mx-auto border-2 border-purple-500 shadow-2xl relative overflow-hidden">
                    <!-- Efeito de brilho -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-purple-500 rounded-full filter blur-3xl opacity-20 animate-pulse"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500 rounded-full filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 1s;"></div>
                    
                    <div class="relative z-10">
                        <!-- Badge -->
                        <div class="inline-block bg-yellow-500 text-gray-900 px-4 py-2 rounded-full text-sm font-bold mb-6 animate-bounce">
                            🚀 OPORTUNIDADE DE NEGÓCIO
                        </div>
                        
                        <!-- Título -->
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                            Quer Ter um Sistema Desse para Você?
                        </h2>
                        <p class="text-xl text-blue-200 mb-8">
                            Adquira uma cópia completa e comece seu próprio negócio de disparos em massa
                        </p>
                        
                        <!-- Preço Destaque -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-8 border border-white/20">
                            <div class="flex items-center justify-center gap-4 mb-4">
                                <div class="text-center">
                                    <div class="text-gray-300 text-sm mb-1">Investimento Único</div>
                                    <div class="text-5xl font-bold text-white">R$ 297</div>
                                    <div class="text-green-400 text-sm mt-1">✓ Pagamento único</div>
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4 mt-6">
                                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-code text-purple-400 text-xl mt-1"></i>
                                        <div>
                                            <div class="font-semibold text-white mb-1">Código-Fonte Completo</div>
                                            <div class="text-sm text-gray-300">Sistema 100% funcional pronto para usar</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-infinity text-blue-400 text-xl mt-1"></i>
                                        <div>
                                            <div class="font-semibold text-white mb-1">Uso Ilimitado</div>
                                            <div class="text-sm text-gray-300">Revenda, white label, personalize à vontade</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Suporte Técnico -->
                        <div class="bg-orange-500/20 border border-orange-500/50 rounded-lg p-4 mb-8">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-headset text-orange-400 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <div class="font-semibold text-white mb-1">Suporte Técnico Opcional</div>
                                    <div class="text-sm text-gray-200">
                                        Precisa de ajuda? Oferecemos suporte técnico especializado por <strong class="text-orange-300">R$ 200/hora</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Requisitos Técnicos -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 mb-6 border border-slate-700 shadow-xl">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-white mb-2">
                                    <i class="fas fa-server text-blue-400 mr-2"></i>
                                    O Que Você Precisa para Rodar o Sistema
                                </h3>
                                <p class="text-gray-400 text-sm">Infraestrutura necessária para funcionamento completo</p>
                            </div>
                            
                            <!-- Grid de Servidores -->
                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <!-- Servidor 1: VPS Evolution API -->
                                <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/20 rounded-lg p-5 border-2 border-blue-500/50 hover:border-blue-400/70 transition-all">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-cloud text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <div class="text-white font-bold text-lg">VPS Evolution API</div>
                                            <div class="text-blue-300 text-xs">Servidor dedicado</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-sm text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-microchip text-blue-400 w-4"></i>
                                            <span><strong>4 CPU</strong> + <strong>4GB RAM</strong></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fab fa-linux text-blue-400 w-4"></i>
                                            <span>Ubuntu/Debian</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-dollar-sign text-green-400 w-4"></i>
                                            <span>R$ 40-80/mês</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Servidor 2: Hospedagem Web -->
                                <div class="bg-gradient-to-br from-purple-600/20 to-purple-800/20 rounded-lg p-5 border-2 border-purple-500/50 hover:border-purple-400/70 transition-all">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-globe text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <div class="text-white font-bold text-lg">Hospedagem Web</div>
                                            <div class="text-purple-300 text-xs">Para o sistema ZAPX</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-sm text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <i class="fab fa-php text-purple-400 w-4"></i>
                                            <span>PHP 7.4+ + MySQL</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-server text-purple-400 w-4"></i>
                                            <span>Compartilhada ou VPS</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-dollar-sign text-green-400 w-4"></i>
                                            <span>R$ 15-50/mês</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vídeo Tutorial -->
                            <div class="bg-gradient-to-r from-red-600/20 to-pink-600/20 rounded-lg p-5 border-2 border-red-500/50 mb-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fab fa-youtube text-white text-2xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-white font-bold text-lg mb-2">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Tutorial: Como Criar VPS com Evolution API
                                        </div>
                                        <p class="text-gray-300 text-sm mb-3">
                                            Assista ao vídeo completo e aprenda passo a passo como configurar sua VPS
                                        </p>
                                        <a href="https://youtu.be/KKZyp9z27hE" target="_blank" 
                                           class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all transform hover:scale-105">
                                            <i class="fab fa-youtube"></i>
                                            Assistir Tutorial no YouTube
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Napoleon Host - Recomendação -->
                            <div class="bg-gradient-to-r from-orange-600/20 to-yellow-600/20 rounded-lg p-5 border-2 border-orange-500/50">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-star text-white text-2xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-white font-bold text-lg">Hospedagem Recomendada</span>
                                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-bold">10% OFF</span>
                                        </div>
                                        <p class="text-gray-300 text-sm mb-3">
                                            <strong>Napoleon Host</strong> - VPS de alta performance com suporte brasileiro
                                        </p>
                                        <div class="flex flex-wrap gap-3">
                                            <a href="https://dantetesta.com.br/napoleon" target="_blank" 
                                               class="inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all transform hover:scale-105">
                                                <i class="fas fa-external-link-alt"></i>
                                                Contratar Napoleon Host
                                            </a>
                                            <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg border border-orange-500/30">
                                                <i class="fas fa-tag text-orange-400"></i>
                                                <span class="text-white text-sm">
                                                    Cupom: <strong class="text-orange-300">DANTE10</strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aviso Importante -->
                        <div class="bg-yellow-500/10 border-2 border-yellow-500/50 rounded-lg p-5 mb-8">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl mt-1"></i>
                                <div class="flex-1">
                                    <div class="font-bold text-yellow-300 mb-2 text-lg">⚠️ Aviso Importante - Leia com Atenção</div>
                                    <div class="text-sm text-gray-200 space-y-2">
                                        <p>
                                            <strong>Este sistema utiliza API não oficial do WhatsApp.</strong> Não há garantias de funcionamento futuro, 
                                            pois as regras são definidas pela Meta (WhatsApp). Mudanças nas políticas podem impactar o serviço.
                                        </p>
                                        <p class="text-yellow-200">
                                            <strong>• Não oferecemos reembolso</strong><br>
                                            <strong>• Produto vendido "como está"</strong><br>
                                            <strong>• Adquira apenas se estiver ciente dos riscos</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CTA -->
                        <div class="text-center">
                            <a href="https://wa.me/5519998021956?text=Olá! Tenho interesse em adquirir uma cópia do sistema ZAPX por R$ 297. Gostaria de mais informações." 
                               target="_blank"
                               class="inline-block bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-xl text-lg font-bold hover:from-green-600 hover:to-green-700 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                                <i class="fab fa-whatsapp mr-2 text-2xl"></i>
                                Adquirir Sistema Agora
                            </a>
                            <p class="text-gray-300 text-sm mt-4">
                                <i class="fas fa-lock mr-1"></i>
                                Pagamento seguro via PIX ou transferência
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-bolt text-blue-500 text-2xl mr-2"></i>
                    <h3 class="text-2xl font-bold text-white">ZAPX</h3>
                </div>
                <p class="text-gray-400 mb-6">
                    Disparo em massa no WhatsApp para empresas
                </p>
                <div class="flex justify-center space-x-6">
                    <a href="https://wa.me/5519998021956" target="_blank" class="text-gray-400 hover:text-green-500 transition">
                        <i class="fab fa-whatsapp text-2xl"></i>
                    </a>
                    <a href="mailto:dante.testa@gmail.com" class="text-gray-400 hover:text-blue-500 transition">
                        <i class="fas fa-envelope text-2xl"></i>
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-700">
                    <p class="text-gray-400 text-sm">
                        © 2025 ZAPX. Todos os direitos reservados. 
                        Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="text-blue-500 hover:text-blue-400">Dante Testa</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Close mobile menu when clicking on links
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('mobile-menu').classList.add('hidden');
            });
        });
    </script>
</body>
</html>
