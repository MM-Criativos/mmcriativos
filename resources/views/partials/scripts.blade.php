<script>
    document.addEventListener("DOMContentLoaded", function() {
        const carousels = document.querySelectorAll('.ogency-owl__carousel');

        carousels.forEach(function(carousel) {
            $(carousel).on('mouseenter', function() {
                $(this).trigger('stop.owl.autoplay');
            });

            $(carousel).on('mouseleave', function() {
                // Reinicia o autoplay do zero, com novo timeout
                $(this).trigger('play.owl.autoplay', [
                    3000
                ]); // 3000ms = 3s (ajuste conforme quiser)
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('processModal');
        const overlay = modal.querySelector('.process-modal__overlay');
        const closeBtn = modal.querySelector('.process-modal__close');

        document.querySelectorAll('.open-process-modal').forEach(btn => {
            btn.addEventListener('click', e => {
                const categoria = e.currentTarget.dataset.category;

                // Aqui você pode carregar dinamicamente os dados (via fetch/AJAX)
                // Exemplo de preenchimento estático:
                document.getElementById('processModalTitle').textContent = categoria ===
                    'wireframes' ?
                    'Wireframe da Home' :
                    'Detalhes do Processo';
                document.getElementById('processModalEtapa').textContent = 'Etapa: ' +
                    categoria;
                document.getElementById('processModalDescricao').textContent =
                    'Descrição da etapa ' + categoria;
                document.getElementById('processModalImage').src = 'assets/images/process/' +
                    categoria + '-1.jpg';

                modal.style.display = 'block';
                document.body.classList.add('modal-open');
            });
        });

        [overlay, closeBtn].forEach(el => el.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }));
    });
</script>
