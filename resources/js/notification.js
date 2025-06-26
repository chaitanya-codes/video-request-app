if (Notification.permission !== "granted") {
    Notification.requestPermission();
}

const sse = new EventSource("/admin/notifications/stream");

sse.addEventListener("order", function (e) {
    const data = JSON.parse(e.data);
    new Notification("🛒 New Video Order", {
        body: `#${data.id}: ${data.video_name} by ${data.user}`
    });
});

sse.onerror = function (e) {
    console.warn("SSE connection lost:", e);
};