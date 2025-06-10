<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Up Ảnh Lấy Link</title>

    <!-- Tải Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tải Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />

    <style>
        /* Áp dụng font Inter cho toàn bộ trang web */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0D1117; /* Màu nền đen tuyền */
        }

        /* Hiệu ứng cho vùng kéo thả */
        .drop-zone {
            transition: background-color 0.2s ease-in-out,
                border-color 0.2s ease-in-out,
                box-shadow 0.2s ease-in-out;
        }
        .drop-zone--over {
            border-color: #3b82f6; /* blue-500 */
            background-color: #161b22;
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.3); /* Thêm hiệu ứng glow khi kéo qua */
        }

        /* Gạch dưới cho tab đang hoạt động */
        .tab-active {
            border-bottom: 3px solid #3b82f6; /* blue-500, làm dày hơn */
            color: white;
        }

        /* Hiệu ứng mờ dần khi phần tử xuất hiện */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="text-slate-300 flex flex-col min-h-screen items-center justify-center p-4">
    <!-- Main Content -->
    <main class="w-full max-w-2xl">
        <!-- Tiêu đề và mô tả -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-3">
                Up <span class="text-green-400">Ảnh</span> Lấy Link
            </h1>
            <p class="text-slate-400 max-w-md mx-auto">
                Dịch vụ lưu trữ ảnh trực tuyến siêu nhanh, đơn giản và an toàn.
                Upload ảnh miễn phí và nhận link trực tiếp chỉ trong vài giây.
            </p>
        </div>

        <!-- Khung chứa bộ tải lên -->
        <div
            class="bg-[#161B22]/70 rounded-xl border border-slate-800 shadow-2xl shadow-black/30 p-2 sm:p-4"
        >
            <!-- Tabs -->
            <div
                class="flex justify-center mb-6 border-b border-slate-800"
            >
                <button
                    class="font-semibold py-3 px-4 -mb-px tab-active"
                >
                    Upload từ máy
                </button>
                <button
                    class="font-semibold py-3 px-4 text-gray-500 hover:text-white hover:border-b-2 hover:border-slate-600 transition-all duration-200"
                >
                    Upload từ URL
                </button>
            </div>

            <!-- Vùng tải lên -->
            <div id="uploadContainer">
                <div
                    id="dropZone"
                    class="drop-zone w-full p-6 md:p-10 text-center bg-transparent border-2 border-dashed border-blue-800 rounded-xl cursor-pointer"
                >
                    <input
                        type="file"
                        id="fileInput"
                        multiple
                        class="hidden"
                    />

                    <div class="flex justify-center mb-4">
                        <svg
                            class="w-12 h-12 text-blue-600"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9A2.25 2.25 0 0018.75 19.5V10.5a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25"
                            />
                        </svg>
                    </div>

                    <p class="text-lg font-semibold text-white mb-4">
                        Kéo thả ảnh hoặc video vào đây
                    </p>

                    <div class="text-sm text-slate-400 space-y-1 mb-4">
                        <p>
                            • Ảnh hỗ trợ: JPG, PNG, GIF, WebP (tối đa 5MB)
                        </p>
                        <p>
                            • Video hỗ trợ: MP4, WebM (tối đa 15MB)
                        </p>
                    </div>

                    <p class="text-gray-500 mb-4">hoặc</p>

                    <button
                        id="browseBtn"
                        class="w-full md:w-auto bg-blue-600 text-white font-bold px-8 py-3 rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/30 ring-1 ring-blue-500/50"
                    >
                        Chọn file từ máy tính
                    </button>
                </div>
            </div>
        </div>

        <!-- Danh sách tệp đã tải lên -->
        <div id="uploadProgress" class="mt-4 w-full">
            <!-- Các tệp đang tải lên sẽ được hiển thị ở đây -->
        </div>
    </main>

    <script>
        // Lấy các phần tử DOM
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const browseBtn = document.getElementById('browseBtn');
        const uploadProgressContainer = document.getElementById('uploadProgress');

        // Hàm xử lý khi nhấp vào nút "Chọn tệp"
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // Hàm xử lý khi nhấp vào vùng kéo thả
        dropZone.addEventListener('click', (e) => {
            // Chỉ mở hộp thoại file khi click vào vùng trống, không phải nút
            if (
                e.target.id === 'dropZone' ||
                (e.target.parentElement.id === 'dropZone' &&
                    e.target.tagName !== 'BUTTON')
            ) {
                fileInput.click();
            }
        });

        // Ngăn chặn hành vi mặc định của trình duyệt khi kéo tệp
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(
            (eventName) => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            }
        );

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Thêm hiệu ứng khi kéo tệp vào vùng
        ['dragenter', 'dragover'].forEach((eventName) => {
            dropZone.addEventListener(
                eventName,
                () => {
                    dropZone.classList.add('drop-zone--over');
                },
                false
            );
        });

        // Bỏ hiệu ứng khi kéo tệp ra ngoài hoặc thả tệp
        ['dragleave', 'drop'].forEach((eventName) => {
            dropZone.addEventListener(
                eventName,
                () => {
                    dropZone.classList.remove('drop-zone--over');
                },
                false
            );
        });

        // Xử lý khi thả tệp
        dropZone.addEventListener('drop', handleDrop, false);

        // Xử lý khi chọn tệp từ hộp thoại
        fileInput.addEventListener('change', function () {
            handleFiles(this.files);
        });

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            handleFiles(files);
        }

        // Hàm chính xử lý các tệp đã chọn/thả
        function handleFiles(files) {
            files = [...files];
            if (files.length === 0) return;
            uploadProgressContainer.innerHTML = ''; // Xóa nội dung cũ
            files.forEach((file) => uploadFile(file));
        }

        // Hàm giả lập việc tải lên và hiển thị thanh tiến trình
        function uploadFile(file) {
            // Tạo phần tử hiển thị thông tin tệp và thanh tiến trình
            const fileElement = document.createElement('div');
            // Thêm border và shadow tinh tế
            fileElement.className =
                'bg-[#161B22] p-4 rounded-lg mb-3 flex items-center justify-between border border-slate-800 shadow-md shadow-black/20 animate-fade-in';

            const fileInfo = document.createElement('div');
            fileInfo.className = 'flex items-center space-x-3 overflow-hidden';

            // Icon file
            const fileIcon = `<svg class="w-6 h-6 text-slate-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>`;
            fileInfo.innerHTML = fileIcon;

            const fileName = document.createElement('span');
            fileName.className = 'text-sm text-white truncate';
            fileName.textContent = file.name;
            fileInfo.appendChild(fileName);

            const progressWrapper = document.createElement('div');
            progressWrapper.className = 'w-1/4 bg-slate-700 rounded-full h-2.5';

            const progressBar = document.createElement('div');
            // Thêm hiệu ứng gradient cho thanh tiến trình
            progressBar.className =
                'bg-gradient-to-r from-blue-500 to-cyan-400 h-2.5 rounded-full transition-all duration-300 ease-linear';
            progressBar.style.width = '0%';

            progressWrapper.appendChild(progressBar);
            fileElement.appendChild(fileInfo);
            fileElement.appendChild(progressWrapper);
            uploadProgressContainer.appendChild(fileElement);

            // Giả lập tiến trình tải lên
            let progress = 0;
            const interval = setInterval(() => {
                progress += 5 + Math.random() * 10;
                if (progress >= 100) {
                    progress = 100;
                    // Đổi sang màu xanh lá khi hoàn thành
                    progressBar.className =
                        'bg-gradient-to-r from-emerald-500 to-green-400 h-2.5 rounded-full';
                    clearInterval(interval);
                }
                progressBar.style.width = progress + '%';
            }, 150);
        }
    </script>
</body>
</html>
