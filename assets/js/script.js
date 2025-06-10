document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileElem');
    const urlInput = document.getElementById('imageUrl');
    const uploadUrlBtn = document.getElementById('uploadUrlBtn');
    const uploadStatus = document.getElementById('uploadStatus');
    const resultSection = document.getElementById('result');
    const errorDiv = document.getElementById('error');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        dropArea.classList.add('hover');
    }
    
    function unhighlight(e) {
        dropArea.classList.remove('hover');
    }
    
    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    // Handle selected files
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        const formData = new FormData();
        
        // Validate file types and sizes before upload
        for(let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check file type
            if (!file.type.match('image/(jpeg|jpg|png)')) {
                showError('Chỉ chấp nhận file ảnh định dạng JPG, JPEG hoặc PNG.');
                return;
            }
            
            // Check file size (10MB = 10 * 1024 * 1024)
            if (file.size > 10 * 1024 * 1024) {
                showError('Kích thước file không được vượt quá 10MB.');
                return;
            }
            
            formData.append('files[]', file);
        }
        
        uploadFiles(formData);
    }
    
    // Handle URL upload
    uploadUrlBtn.addEventListener('click', function() {
        const url = urlInput.value.trim();
        
        if (!url) {
            showError('Vui lòng nhập URL ảnh.');
            return;
        }
        
        if (!isValidImageUrl(url)) {
            showError('URL ảnh không hợp lệ.');
            return;
        }
        
        uploadImageUrl(url);
    });
    
    function isValidImageUrl(url) {
        try {
            const urlObj = new URL(url);
            return /\.(jpg|jpeg|png)$/i.test(urlObj.pathname);
        } catch {
            return false;
        }
    }
    
    // Upload functions
    function uploadFiles(formData) {
        showLoading();
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(handleUploadResponse)
        .catch(handleUploadError);
    }
    
    function uploadImageUrl(url) {
        showLoading();
        
        fetch('upload_url.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ imageUrl: url })
        })
        .then(response => response.json())
        .then(handleUploadResponse)
        .catch(handleUploadError);
    }
    
    // Response handlers
    function handleUploadResponse(data) {
        hideLoading();
        
        if (data.error) {
            showError(data.error);
            return;
        }
        
        displayResults(data);
        clearInputs();
    }
    
    function handleUploadError(error) {
        hideLoading();
        showError('Có lỗi xảy ra khi tải ảnh lên. Vui lòng thử lại sau.');
    }
    
    // UI update functions
    function showLoading() {
        uploadStatus.classList.remove('hidden');
        resultSection.classList.add('hidden');
        errorDiv.classList.add('hidden');
    }
    
    function hideLoading() {
        uploadStatus.classList.add('hidden');
    }
    
    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
        resultSection.classList.add('hidden');
    }
    
    function displayResults(data) {
        document.getElementById('viewLink').textContent = data.viewLink;
        document.getElementById('directLink').textContent = data.directLink;
        document.getElementById('htmlCode').textContent = data.htmlCode;
        document.getElementById('markdownCode').textContent = data.markdownCode;
        
        // Make links clickable
        document.getElementById('viewLink').href = data.viewLink;
        document.getElementById('directLink').href = data.directLink;
        
        resultSection.classList.remove('hidden');
    }
    
    function clearInputs() {
        fileInput.value = '';
        urlInput.value = '';
    }
    
    // Copy to clipboard functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const container = this.closest('.result-content');
            const element = container.querySelector('a, code');
            const textToCopy = element.textContent;

            try {
                await navigator.clipboard.writeText(textToCopy);
                
                // Visual feedback
                this.setAttribute('data-copied', 'true');
                const originalText = this.querySelector('.copy-text').textContent;
                this.querySelector('.copy-text').textContent = 'Đã sao chép!';
                
                // Reset after 2 seconds
                setTimeout(() => {
                    this.removeAttribute('data-copied');
                    this.querySelector('.copy-text').textContent = originalText;
                }, 2000);
            } catch (err) {
                showError('Không thể sao chép. Vui lòng thử lại.');
            }
        });
    });
});
