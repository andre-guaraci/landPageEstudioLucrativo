document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Inicializa o Swiper (o carrossel)
    const swiper = new Swiper('.product-carousel', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 30 },
            1024: { slidesPerView: 3, spaceBetween: 40 }
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // 2. Função para buscar os produtos e adicioná-los ao carrossel
    async function carregarProdutos() {
        try {
            // ALTERAÇÃO IMPORTANTE: Agora busca os dados do script PHP
            const response = await fetch('get_products.php');
            if (!response.ok) {
                throw new Error('Falha ao buscar produtos da API.');
            }
            const produtos = await response.json();

            const productListContainer = document.getElementById('product-list');
            productListContainer.innerHTML = ''; // Limpa o container antes de adicionar

            // Cria o HTML para cada produto e o adiciona ao carrossel
            produtos.forEach(produto => {               
                    const slideHTML = `
                        <div class="swiper-slide">
                            <div class="product-card">
                                <img src="${produto.imagem}" alt="${produto.nome}" onerror="this.onerror=null;this.src='https://placehold.co/600x400/1a1a1a/FFA500?text=Imagem';"/>
                                <div class="product-info">
                                    <h3>${produto.nome}</h3>
                                    <p>${produto.descricao}</p>
                                    <!-- ALTERAÇÃO AQUI: Aponta diretamente para o link de checkout em uma nova aba -->
                                    <a href="${produto.link}"  class="product-link">Saber Mais</a>
                                </div>
                            </div>
                        </div>
                    `;  
                productListContainer.innerHTML += slideHTML;
            });
            
            // Atualiza o Swiper para ele reconhecer os novos slides
            swiper.update();

        } catch (error) {
            console.error('Erro ao carregar os produtos:', error);
            const productListContainer = document.getElementById('product-list');
            productListContainer.innerHTML = '<p style="color: white; text-align: center;">Não foi possível carregar os cursos no momento. Tente novamente mais tarde.</p>';
        }
    }

    // 3. Chama a função para carregar os produtos
    carregarProdutos();
});


// Lógica para o Acordeão do FAQ

// Seleciona todos os itens do FAQ
const faqItems = document.querySelectorAll('.faq-item');

// Adiciona um evento de clique a cada pergunta
faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    question.addEventListener('click', () => {
        // Seleciona a resposta correspondente
        const answer = item.querySelector('.faq-answer');
        
        // Verifica se o item já está ativo
        const isActive = item.classList.contains('active');

        // Primeiro, remove a classe 'active' de todos os itens para fechar os abertos
        faqItems.forEach(i => {
            i.classList.remove('active');
            i.querySelector('.faq-answer').style.maxHeight = null;
        });

        // Se o item clicado não estava ativo, abre ele
        if (!isActive) {
            item.classList.add('active');
            // Define o maxHeight para a altura real do conteúdo para criar a animação
            answer.style.maxHeight = answer.scrollHeight + "px";
        }
    });
});