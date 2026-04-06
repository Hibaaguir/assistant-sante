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

    function success(message, title = "Success") {
        return add({ type: "success", title, message });
    }

    function error(message, title = "Error") {
        return add({ type: "error", title, message, duration: 6000 });
    }

    function warning(message, title = "Warning") {
        return add({ type: "warning", title, message, duration: 5000 });
    }

    function info(message, title = "Information") {
        return add({ type: "info", title, message });
    }

    function itemAdded(message = "Item added successfully.") {
        return success(message, "Added");
    }

    function itemUpdated(message = "Item updated successfully.") {
        return info(message, "Updated");
    }

    function itemDeleted(message = "Item deleted successfully.") {
        return success(message, "Deleted");
    }

    function actionCancelled(message = "Action cancelled successfully.") {
        return warning(message, "Cancelled");
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
