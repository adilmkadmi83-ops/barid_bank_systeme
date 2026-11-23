// ===== script.js =====
// 1) Messages de succès qui disparaissent  2) Formulaire qui s'ouvre/se ferme  3) Confirmation avant suppression

document.addEventListener("DOMContentLoaded", function() {

    // 1) Faire disparaître le message de succès après 3 secondes
    const successMsg = document.getElementById("successMessage");
    if (successMsg) {
        setTimeout(function() {
            successMsg.style.display = "none";
        }, 3000);
    }

    // 2) Bouton qui ouvre / ferme le formulaire (déjà ouvert en cas d'erreur ou de modification)
    const bouton = document.getElementById("btnFormulaire");
    const formBox = document.getElementById("formBox");
    if (bouton && formBox) {
        if (formBox.dataset.ouvert !== "1") {
            formBox.classList.add("cache");
        }
        bouton.addEventListener("click", function() {
            formBox.classList.toggle("cache");
        });
    }

    // 3) Demander une confirmation avant d'envoyer un formulaire de suppression
    document.querySelectorAll(".form-suppr").forEach(function(form) {
        form.addEventListener("submit", function(event) {
            const question = form.dataset.confirm || "Voulez-vous vraiment supprimer cet élément ?";
            if (!confirm(question)) {
                event.preventDefault();
            }
        });
    });
});


















































