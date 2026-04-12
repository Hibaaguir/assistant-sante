import { defineStore } from "pinia";
import { ref } from "vue";

let seq = 0;

export const useNotificationsStore = defineStore("notifications", () => {
    const items = ref([]);

    function add({
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
            setTimeout(() => remove(id), duration);
        }

        return id;
    }

    function remove(id) {
        items.value = items.value.filter((item) => item.id !== id);
    }

    function clear() {
        items.value = [];
    }

    function success(message, title = "Succès") {
        return add({ type: "success", title, message });
    }

    function error(message, title = "Erreur") {
        return add({ type: "error", title, message, duration: 6000 });
    }

    function warning(message, title = "Avertissement") {
        return add({ type: "warning", title, message, duration: 5000 });
    }

    function info(message, title = "Information") {
        return add({ type: "info", title, message });
    }

    function itemAdded(message = "L'élément a été ajouté avec succès.") {
        return success(message, "Ajouté");
    }

    function itemUpdated(message = "L'élément a été mis à jour avec succès.") {
        return info(message, "Mis à jour");
    }

    function itemDeleted(message = "L'élément a été supprimé avec succès.") {
        return success(message, "Supprimé");
    }

    function actionCancelled(message = "L'action a été annulée avec succès.") {
        return warning(message, "Annulé");
    }

    return {
        items,
        add,
        remove,
        clear,
        success,
        error,
        warning,
        info,
        itemAdded,
        itemUpdated,
        itemDeleted,
        actionCancelled,
    };
});
