import { defineStore } from "pinia";
import { ref } from "vue";

let seq = 0;

export const useNotificationsStore = defineStore("notifications", () => {
    const items = ref([]);

    function ajouter({
        type = "info",
        title = "",
        message = "",
        duration = 4500,
    } = {}) {
        const id = ++seq;
        items.value.push({
            id,
            type,
            title,
            message,
            duration,
            createdAt: Date.now(),
        });

        if (duration > 0) {
            setTimeout(() => retirer(id), duration);
        }

        return id;
    }

    function retirer(id) {
        items.value = items.value.filter((item) => item.id !== id);
    }

    function vider() {
        items.value = [];
    }

    function succes(message, title = "Succes") {
        return ajouter({ type: "success", title, message });
    }

    function erreur(message, title = "Erreur") {
        return ajouter({ type: "error", title, message, duration: 6000 });
    }

    function avertissement(message, title = "Attention") {
        return ajouter({ type: "warning", title, message, duration: 5000 });
    }

    function information(message, title = "Information") {
        return ajouter({ type: "info", title, message });
    }

    function actionAjoutee(message = "Element ajoute avec succes.") {
        return succes(message, "Ajout");
    }

    function actionModifiee(message = "Element modifie avec succes.") {
        return information(message, "Modification");
    }

    function actionSupprimee(message = "Element supprime avec succes.") {
        return erreur(message, "Suppression");
    }

    function actionAnnulee(message = "Action annulee avec succes.") {
        return avertissement(message, "Annulation");
    }

    return {
        items,
        ajouter,
        retirer,
        vider,
        succes,
        erreur,
        avertissement,
        information,
        actionAjoutee,
        actionModifiee,
        actionSupprimee,
        actionAnnulee,
    };
});
