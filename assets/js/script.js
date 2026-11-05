/**
 * JavaScript do projeto (puro, sem bibliotecas).
 * Aqui ficam só as validações dos formulários e o botão de mostrar a senha.
 * A validação que vale de verdade é a do PHP, no servidor.
 */
document.addEventListener('DOMContentLoaded', function () {

    // 1) Validação dos formulários
    var formularios = document.querySelectorAll('.needs-validation');

    formularios.forEach(function (form) {
        var senha = form.querySelector('#senha');
        var confirmar = form.querySelector('#confirmar_senha');

        // Cadastro: a confirmação precisa ser igual à senha
        if (senha && confirmar) {
            var conferirSenhas = function () {
                if (confirmar.value !== senha.value) {
                    confirmar.setCustomValidity('As senhas não coincidem.');
                } else {
                    confirmar.setCustomValidity('');
                }
            };
            senha.addEventListener('input', conferirSenhas);
            confirmar.addEventListener('input', conferirSenhas);
        }

        // Ao enviar: se tiver campo inválido, não envia e mostra os avisos
        form.addEventListener('submit', function (evento) {
            if (!form.checkValidity()) {
                evento.preventDefault();
            }
            form.classList.add('was-validated');
        });
    });

    // 2) Botão do olho: mostra ou esconde a senha
    var botoesSenha = document.querySelectorAll('.btn-toggle-senha');

    botoesSenha.forEach(function (botao) {
        botao.addEventListener('click', function () {
            var campo = document.querySelector(botao.getAttribute('data-alvo'));
            var icone = botao.querySelector('i');

            if (campo.type === 'password') {
                campo.type = 'text';
                icone.className = 'bi bi-eye-slash';
            } else {
                campo.type = 'password';
                icone.className = 'bi bi-eye';
            }
        });
    });
});
