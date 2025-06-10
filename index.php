<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wepener.org</title>
    
    <!-- Tải Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tải Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0D1117;
            min-height: 100vh;
        }
        .nav-button {
            background: linear-gradient(to right, #2F81F7, #2871D9);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .nav-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
        }
        .nav-button:hover::after {
            left: 100%;
        }
        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(47, 129, 247, 0.2);
        }
        .nav-button:active {
            transform: translateY(0);
        }
        .drop-zone {
            transition: all 0.2s ease;
            border: 2px dashed rgba(99, 102, 241, 0.3);
            background: #1C2128;
        }
        .drop-zone:hover, .drop-zone--over {
            border-color: rgba(99, 102, 241, 0.5);
            background: #1C2128;
        }
        .upload-container {
            background: #1C2128;
            border: 1px solid #30363D;
        }
        .tab {
            position: relative;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .tab-active {
            color: #fff;
            border-bottom: 2px solid #2F81F7;
        }
        .tab:not(.tab-active) {
            color: rgba(255, 255, 255, 0.6);
        }
        .tab:hover:not(.tab-active) {
            color: rgba(255, 255, 255, 0.8);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .btn-primary {
            background: linear-gradient(to right, #2F81F7, #2871D9);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
        }
        .btn-primary:hover::after {
            left: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(47, 129, 247, 0.2);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        input, select {
            background-color: #0D1117;
            border-color: #30363D;
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: #2F81F7;
            box-shadow: 0 0 0 2px rgba(47, 129, 247, 0.2);
        }
        .result-link {
            background: #0D1117;
            border: 1px solid #30363D;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-family: monospace;
            word-break: break-all;
            transition: all 0.3s ease;
        }
        .result-link:hover {
            border-color: #2F81F7;
            background: rgba(47, 129, 247, 0.1);
        }
    </style>
</head>
<body class="text-white">
    <!-- Navigation -->
    <nav class="px-6 py-4 flex justify-between items-center bg-[#161B22] border-b border-[#30363D]">
        <div class="flex items-center space-x-2">
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span class="text-xl font-bold">wepener.org</span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="https://chungchi24h.com/" target="_blank" class="nav-button">Mua chứng chỉ ios</a>
            <a href="#" class="nav-button">Về chúng tôi</a>
            <a href="#" class="nav-button">Liên hệ</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 pt-8 pb-16">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4">wepener.org</h1>
            <p class="text-lg text-white/90 mb-4 max-w-2xl mx-auto">
                Dịch vụ lưu trữ ảnh trực tuyến siêu nhanh, đơn giản và an toàn. Upload ảnh miễn phí và 
                nhận link trực tiếp chỉ trong vài giây với wepener.org.
            </p>
        </div>

        <!-- Upload Container -->
        <div class="upload-container rounded-xl overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-[#30363D]">
                <button id="localTab" class="tab tab-active flex-1 font-medium py-3 text-center">Upload từ máy</button>
                <button id="urlTab" class="tab flex-1 font-medium py-3 text-center">Upload từ URL</button>
            </div>

            <!-- Upload Area - Local Files -->
            <div id="localUpload" class="p-6">
                <div class="drop-zone w-full p-12 rounded-xl text-center cursor-pointer">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="file" id="fileInput" name="files[]" multiple accept="image/*,video/*" class="hidden">
                        
                        <div class="flex justify-center mb-6">
                            <svg class="w-16 h-16 text-[#2F81F7]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 17.607c1.494-.585 3-2.025 3-4.107 0-3-2.691-5-6-5a5.978 5.978 0 0 0-4.797 2.397A6.989 6.989 0 0 0 8 10C4.686 10 2 12.686 2 16s2.686 6 6 6h11.25c2.071 0 3.75-1.679 3.75-3.75 0-1.462-.85-2.723-2.083-3.321"/>
                            </svg>
                        </div>
                        
                        <p class="text-xl font-medium mb-2">Kéo thả nhiều ảnh vào đây</p>
                        <p class="text-white/70 mb-6">hoặc</p>
                        
                        <button id="browseBtn" type="button" class="btn-primary px-6 py-3 rounded-lg font-medium text-white">
                            Chọn nhiều file ảnh/video
                        </button>

                        <div class="mt-4 text-sm text-white/70">
                            Giới hạn: Ảnh tối đa 5MB, Video tối đa 15MB
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Area - URL -->
            <div id="urlUpload" class="hidden p-6">
                <div class="drop-zone w-full p-12 rounded-xl text-center">
                    <div class="max-w-xl mx-auto space-y-6">
                        <input type="text" 
                               id="imageUrl"
                               placeholder="Nhập URL ảnh hoặc video vào đây" 
                               class="w-full bg-[#0D1117] border border-[#30363D] rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-[#2F81F7]">
                        
                        <button onclick="handleUrlUpload()" 
                                class="btn-primary px-6 py-3 rounded-lg font-medium text-white w-full">
                            Upload từ URL
                        </button>

                        <div class="text-sm text-white/70">
                            Giới hạn: Ảnh tối đa 5MB, Video tối đa 15MB
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Results -->
        <div id="uploadResults" class="mt-6 space-y-4">
            <!-- Results will be inserted here -->
        </div>
    </main>

    <script>
        // Tab switching
        const localTab = document.getElementById('localTab');
        const urlTab = document.getElementById('urlTab');
        const localUpload = document.getElementById('localUpload');
        const urlUpload = document.getElementById('urlUpload');

        localTab.addEventListener('click', () => {
            localTab.classList.add('tab-active');
            urlTab.classList.remove('tab-active');
            localUpload.classList.remove('hidden');
            urlUpload.classList.add('hidden');
        });

        urlTab.addEventListener('click', () => {
            urlTab.classList.add('tab-active');
            localTab.classList.remove('tab-active');
            urlUpload.classList.remove('hidden');
            localUpload.classList.add('hidden');
        });

        // URL Upload handling
        async function handleUrlUpload() {
            const imageUrl = document.getElementById('imageUrl').value;
            if (!imageUrl) return;

            try {
                const formData = new FormData();
                formData.append('url', imageUrl);

                const response = await fetch('upload_url.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showUploadResult(result);
                    document.getElementById('imageUrl').value = '';
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert(error.message || 'Có lỗi xảy ra khi tải file.');
            }
        }

        // File upload handling
        const dropZone = document.querySelector('.drop-zone');
        const fileInput = document.getElementById('fileInput');
        const browseBtn = document.getElementById('browseBtn');
        const uploadForm = document.getElementById('uploadForm');

        browseBtn.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('click', (e) => {
            if (e.target === dropZone) fileInput.click();
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('drop-zone--over');
        }

        function unhighlight(e) {
            dropZone.classList.remove('drop-zone--over');
        }

        dropZone.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFiles);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({ target: { files } });
        }

        async function handleFiles(e) {
            const files = Array.from(e.target.files);
            if (!files.length) return;

            try {
                const formData = new FormData();
                files.forEach(file => {
                    formData.append('files[]', file);
                });

                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    result.files.forEach(file => showUploadResult(file));
                    uploadForm.reset();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert(error.message || 'Có lỗi xảy ra khi tải file.');
            }
        }

        function showUploadResult(result) {
            const resultDiv = document.createElement('div');
            resultDiv.className = 'bg-[#1C2128] border border-[#30363D] p-6 rounded-xl animate-fade-in';
            
            const isImage = result.type === 'image';
            const icon = isImage ? 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' 
                                : 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z';

            resultDiv.innerHTML = `
                <div class="flex items-center mb-4">
                    <svg class="w-5 h-5 text-white/60 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}"/>
                    </svg>
                    <span class="text-white/90">${result.name || 'File đã tải lên'}</span>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-white/70 block mb-1">Link trực tiếp:</label>
                        <div class="result-link">${result.url}</div>
                    </div>
                </div>
            `;

            const resultsContainer = document.getElementById('uploadResults');
            resultsContainer.insertBefore(resultDiv, resultsContainer.firstChild);
        }
    </script>
</body>
</html>
