import L from "leaflet";
import "leaflet/dist/leaflet.css";

import AttendanceMapV2 from "./modules/AttendanceMapV2";

window.AttendanceMapV2 = AttendanceMapV2;

document.addEventListener("livewire:navigated", () => {
    const element = document.getElementById("attendance-map");

    if (!element) {
        AttendanceMapV2.destroyAll();
        return;
    }

    const map = new AttendanceMapV2(element);

    map.init();
});
