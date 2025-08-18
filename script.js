
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