document.addEventListener("DOMContentLoaded", () => {
  const btnStart = document.getElementById("start-export");
  const btnCancel = document.getElementById("cancel-export");
  const bar = document.getElementById("progress-bar");
  const text = document.getElementById("progress-text");
  const progressContainer = document.getElementById("progress-container");

  let cancelRequested = false;

  // Read all data-* attributes into an object
  function getButtonData() {
    const data = {};
    if (!btnStart?.dataset) return data;
    for (const [key, value] of Object.entries(btnStart.dataset)) {
      data[key] = value;
    }

    return data;
  }

  async function postData(action, data = {}) {
    const formData = new FormData();
    formData.append("action", action);
    formData.append("security", aadvanaExport.nonce);
    for (const key in data) formData.append(key, data[key]);

    const response = await fetch(aadvanaExport.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      body: formData,
    });
    if (!response.ok) throw new Error(aadvanaExport.i18n.networkError);
    return response.json();
  }

  async function cleanupFile() {
    try {
      await postData("aadvana_export_large_csv_cleanup");
    } catch (e) {
      console.warn("Cleanup failed:", e);
    }
  }

  async function processExport() {
    let batch = 0;
    cancelRequested = false;

    // Read configuration from button dataset
    const config = getButtonData();
    const batchSize = parseInt(config.batchSize || 500, 10);

    btnStart.disabled = true;
    btnStart.textContent = aadvanaExport.i18n.exporting;
    btnCancel.style.display = "inline-block";
    btnCancel.disabled = false;
    progressContainer.style.display = "block";

    // Show progress text when export starts
    text.style.display = "block";
    text.textContent = aadvanaExport.i18n.starting;

    bar.style.width = "0%";

    try {
      while (true) {
        if (cancelRequested) {
          text.textContent = aadvanaExport.i18n.cancelled;
          btnStart.textContent = aadvanaExport.i18n.csvExportBtnTitle;
          await cleanupFile();
          break;
        }

        const res = await postData("aadvana_export_large_csv", {
          batch,
          batch_size: batchSize,
          ...config,
        });

        if (!res.success) throw new Error(res.data?.message || aadvanaExport.i18n.error);

        if (res.data.done) {
          bar.style.width = "100%";
          text.textContent = aadvanaExport.i18n.completed;
          await new Promise(r => setTimeout(r, 500));
          window.location.href = res.data.download_url;

          // Hide progress UI after short delay
          setTimeout(() => {
            progressContainer.style.display = "none";
            text.style.display = "none"; // 👈 hide progress text again
            btnCancel.style.display = "none";
          }, 1500);

          setTimeout(cleanupFile, 10000);
          break;
        } else {
          const { processed, total } = res.data;
          const percent = Math.min((processed / total) * 100, 100);
          bar.style.width = `${percent}%`;
          text.textContent = `${aadvanaExport.i18n.exporting} ${Math.round(percent)}%`;
          batch = res.data.next_batch;
        }
      }
    } catch (error) {
      console.error(error);
      alert(`${aadvanaExport.i18n.error} ${error.message}`);
      await cleanupFile();
    } finally {
      btnStart.disabled = false;
      btnStart.textContent = aadvanaExport.i18n.csvExportBtnTitle;
      btnCancel.style.display = "none";
      // Ensure hidden after failure or cancel
      text.style.display = "none"; // 👈 hide progress text again
      progressContainer.style.display = "none";
    }
  }

  btnStart?.addEventListener("click", processExport);
  btnCancel?.addEventListener("click", () => {
    cancelRequested = true;
    btnCancel.disabled = true;
    btnStart.textContent = aadvanaExport.i18n.csvExportBtnTitle;
    text.textContent = aadvanaExport.i18n.cancelled;
  });
});