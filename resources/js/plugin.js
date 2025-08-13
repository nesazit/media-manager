// Media Manager Plugin
console.log('---------------dazgilet--------------')
document.addEventListener('alpine:init', () => {
    Alpine.data('mediaManager', () => ({
        isDragOver: false,

        init() {
            // Initialize media manager
        },

        handleDrop(e) {
            this.isDragOver = false;
            // Handle file drop
            if (e.dataTransfer?.files?.length) {
                this.$wire.set('uploadFiles', Array.from(e.dataTransfer.files));
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
