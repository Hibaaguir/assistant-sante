import { defineStore } from "pinia";
import { ref } from "vue";

let seq = 0;

export const useNotificationsStore = defineStore("notifications", () => {
  const items = ref([]);

  function push({ type = "info", title = "", message = "", duration = 3800 } = {}) {
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

  function success(message, title = "Succes") {
    return push({ type: "success", title, message });
  }

  function error(message, title = "Erreur") {
    return push({ type: "error", title, message, duration: 5200 });
  }

  function warning(message, title = "Attention") {
    return push({ type: "warning", title, message, duration: 4600 });
  }

  function info(message, title = "Information") {
    return push({ type: "info", title, message });
  }

  function actionAdded(message = "Element ajoute avec succes.") {
    return success(message, "Ajout");
  }

  function actionUpdated(message = "Element modifie avec succes.") {
    return info(message, "Modification");
  }

  function actionDeleted(message = "Element supprime avec succes.") {
    return error(message, "Suppression");
  }

  function actionCanceled(message = "Action annulee avec succes.") {
    return warning(message, "Annulation");
  }

  return {
    items,
    push,
    remove,
    clear,
    success,
    error,
    warning,
    info,
    actionAdded,
    actionUpdated,
    actionDeleted,
    actionCanceled,
  };
});
