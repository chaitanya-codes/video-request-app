if (Notification.permission !== "granted") {
    Notification.requestPermission();
}

const sse = new EventSource("/admin/notifications/stream");

sse.addEventListener("order", function (e) {
    const data = JSON.parse(e.data);
    const notification = new Notification("🛒 New Video Order", {
        body: `#${data.id}: ${data.video_name} by ${data.user}`,
        tag: `order-${data.id}`,
        data: {
            url: `/admin/orders/${data.id}`
        }
    });
    console.log(notification)
    notification.onclick = (e) => {
        window.focus();
        window.location.href = notification.data.url
    }
});

sse.onerror = function (e) {
    console.warn("SSE connection lost:", e);
};