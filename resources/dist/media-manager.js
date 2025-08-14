(() => {
  // resources/js/plugin.js
  console.log("---------------dazgilet--------------");
  document.addEventListener("alpine:init", () => {
    Alpine.data("mediaManager", () => ({
      isDragOver: false,
      init() {
      },
      handleDrop(e) {
        this.isDragOver = false;
        if (e.dataTransfer?.files?.length) {
          this.$wire.set("uploadFiles", Array.from(e.dataTransfer.files));
        }
      },
      handleDragOver() {
        this.isDragOver = true;
      },
      handleDragLeave() {
        this.isDragOver = false;
      }
    }));
  });
})();
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsiLi4vanMvcGx1Z2luLmpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyIvLyBNZWRpYSBNYW5hZ2VyIFBsdWdpblxuY29uc29sZS5sb2coJy0tLS0tLS0tLS0tLS0tLWRhemdpbGV0LS0tLS0tLS0tLS0tLS0nKVxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignYWxwaW5lOmluaXQnLCAoKSA9PiB7XG4gICAgQWxwaW5lLmRhdGEoJ21lZGlhTWFuYWdlcicsICgpID0+ICh7XG4gICAgICAgIGlzRHJhZ092ZXI6IGZhbHNlLFxuXG4gICAgICAgIGluaXQoKSB7XG4gICAgICAgICAgICAvLyBJbml0aWFsaXplIG1lZGlhIG1hbmFnZXJcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVEcm9wKGUpIHtcbiAgICAgICAgICAgIHRoaXMuaXNEcmFnT3ZlciA9IGZhbHNlO1xuICAgICAgICAgICAgLy8gSGFuZGxlIGZpbGUgZHJvcFxuICAgICAgICAgICAgaWYgKGUuZGF0YVRyYW5zZmVyPy5maWxlcz8ubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgdGhpcy4kd2lyZS5zZXQoJ3VwbG9hZEZpbGVzJywgQXJyYXkuZnJvbShlLmRhdGFUcmFuc2Zlci5maWxlcykpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuXG4gICAgICAgIGhhbmRsZURyYWdPdmVyKCkge1xuICAgICAgICAgICAgdGhpcy5pc0RyYWdPdmVyID0gdHJ1ZTtcbiAgICAgICAgfSxcblxuICAgICAgICBoYW5kbGVEcmFnTGVhdmUoKSB7XG4gICAgICAgICAgICB0aGlzLmlzRHJhZ092ZXIgPSBmYWxzZTtcbiAgICAgICAgfVxuICAgIH0pKTtcbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjs7QUFDQSxVQUFRLElBQUk7QUFDWixXQUFTLGlCQUFpQixlQUFlLE1BQU07QUFDM0MsV0FBTyxLQUFLLGdCQUFnQixNQUFPO0FBQUEsTUFDL0IsWUFBWTtBQUFBLE1BRVosT0FBTztBQUFBO0FBQUEsTUFJUCxXQUFXLEdBQUc7QUFDVixhQUFLLGFBQWE7QUFFbEIsWUFBSSxFQUFFLGNBQWMsT0FBTyxRQUFRO0FBQy9CLGVBQUssTUFBTSxJQUFJLGVBQWUsTUFBTSxLQUFLLEVBQUUsYUFBYTtBQUFBO0FBQUE7QUFBQSxNQUloRSxpQkFBaUI7QUFDYixhQUFLLGFBQWE7QUFBQTtBQUFBLE1BR3RCLGtCQUFrQjtBQUNkLGFBQUssYUFBYTtBQUFBO0FBQUE7QUFBQTsiLAogICJuYW1lcyI6IFtdCn0K
