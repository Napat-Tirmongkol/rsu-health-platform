<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Scanner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Staff Tools</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">Identity Scanner</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Scan a user's identity QR, verify the signed payload, and check in an eligible booking from one place.
                </p>
            </div>

            <div class="text-right">
                <p class="text-sm font-semibold text-slate-700">{{ auth('staff')->user()->full_name ?: auth('staff')->user()->email }}</p>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Clinic {{ session('clinic_id', currentClinicId()) }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Camera Scanner</h2>
                        <p class="text-sm text-slate-500">Chrome on Android can scan directly with BarcodeDetector.</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="start-camera" type="button" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                            Start camera
                        </button>
                        <button id="stop-camera" type="button" class="rounded-2xl bg-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-300">
                            Stop
                        </button>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-3xl border border-slate-200 bg-slate-950">
                    <video id="camera-preview" autoplay muted playsinline class="aspect-[4/3] w-full object-cover"></video>
                </div>

                <p id="camera-status" class="mt-4 text-sm font-semibold text-slate-500">
                    Camera is idle. You can also paste the QR payload manually below.
                </p>

                <div class="mt-6">
                    <label for="qr-payload" class="mb-2 block text-sm font-bold text-slate-700">QR payload</label>
                    <textarea id="qr-payload" rows="8" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>

                <div class="mt-4">
                    <label for="qr-image" class="mb-2 block text-sm font-bold text-slate-700">QR image fallback</label>
                    <label for="qr-image" class="flex cursor-pointer items-center justify-center gap-3 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50">
                        <span>Choose an image with a QR code</span>
                    </label>
                    <input id="qr-image" type="file" accept="image/*" class="hidden">
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button id="verify-payload" type="button" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800">
                        Verify identity
                    </button>
                    <button id="clear-payload" type="button" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Clear
                    </button>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Verification Result</h2>
                        <p class="text-sm text-slate-500">We only enable check-in for pending or confirmed bookings.</p>
                    </div>
                    <div id="result-pill" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Waiting
                    </div>
                </div>

                <div id="feedback" class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm font-semibold"></div>

                <div class="mt-4">
                    <label for="check-in-note" class="mb-2 block text-sm font-bold text-slate-700">Check-in note</label>
                    <textarea id="check-in-note" rows="3" class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Optional note for exceptions, walk-in context, or staff remarks"></textarea>
                </div>

                <div id="identity-card" class="mt-4 hidden rounded-3xl bg-emerald-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Identity</p>
                    <h3 id="identity-name" class="mt-2 text-2xl font-black text-slate-900"></h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Type</p>
                            <p id="identity-type" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                        <div class="rounded-2xl bg-white px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Identifier</p>
                            <p id="identity-value" class="mt-2 text-sm font-bold text-slate-900"></p>
                        </div>
                    </div>
                </div>

                <div id="duplicate-warning" class="mt-4 hidden rounded-3xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-700">Duplicate warning</p>
                    <p id="duplicate-warning-text" class="mt-2 text-sm font-semibold text-amber-900"></p>
                </div>

                <div class="mt-6">
                    <div class="mb-4 grid gap-3 rounded-3xl bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <label for="campaign-filter" class="mb-2 block text-xs font-black uppercase tracking-[0.2em] text-slate-500">Campaign filter</label>
                            <select id="campaign-filter" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                                <option value="">All campaigns</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                                @endforeach
                            </select>
                            @if ($boundCampaign)
                                <p class="mt-2 text-xs font-semibold text-emerald-700">
                                    Scanner is locked to {{ $boundCampaign['title'] }}.
                                </p>
                            @endif
                        </div>
                        <label class="mt-6 flex items-center gap-3 rounded-2xl bg-white px-4 py-3 sm:mt-0">
                            <input id="today-only" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-slate-700">Today only</span>
                        </label>
                    </div>

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-700">Bookings</h3>
                        <span id="booking-count" class="text-xs font-bold text-slate-400">0 items</span>
                    </div>

                    <div id="booking-list" class="space-y-3">
                        <div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                            No identity has been verified yet.
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-700">Recent scans</h3>
                        <span id="recent-scan-count" class="text-xs font-bold text-slate-400">{{ count($recentScans) }} items</span>
                    </div>

                    <div id="recent-scan-list" class="space-y-3">
                        @forelse ($recentScans as $scan)
                            <div class="rounded-3xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ $scan['user_name'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $scan['identity_label'] }}: {{ $scan['identity_value'] }}</p>
                                        <p class="mt-2 text-sm text-slate-500">{{ $scan['campaign_title'] }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $scan['slot_label'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">{{ $scan['relative_time'] }}</p>
                                        <p class="mt-2 text-xs text-slate-400">{{ $scan['staff_name'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                                No recent check-ins yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const verifyUrl = @json(route('staff.scan.verify'));
        const checkInUrl = @json(route('staff.scan.check-in'));
        const csrfToken = @json(csrf_token());
        const boundCampaign = @json($boundCampaign);

        const cameraPreview = document.getElementById('camera-preview');
        const cameraStatus = document.getElementById('camera-status');
        const qrPayload = document.getElementById('qr-payload');
        const qrImageInput = document.getElementById('qr-image');
        const campaignFilter = document.getElementById('campaign-filter');
        const todayOnly = document.getElementById('today-only');
        const feedback = document.getElementById('feedback');
        const checkInNote = document.getElementById('check-in-note');
        const resultPill = document.getElementById('result-pill');
        const identityCard = document.getElementById('identity-card');
        const duplicateWarning = document.getElementById('duplicate-warning');
        const duplicateWarningText = document.getElementById('duplicate-warning-text');
        const identityName = document.getElementById('identity-name');
        const identityType = document.getElementById('identity-type');
        const identityValue = document.getElementById('identity-value');
        const bookingCount = document.getElementById('booking-count');
        const bookingList = document.getElementById('booking-list');
        const recentScanCount = document.getElementById('recent-scan-count');
        const recentScanList = document.getElementById('recent-scan-list');

        let activeStream = null;
        let barcodeDetector = null;
        let scanFrame = null;
        let isBusy = false;
        let currentPayload = '';

        if (boundCampaign) {
            campaignFilter.value = String(boundCampaign.id);
            campaignFilter.setAttribute('disabled', 'disabled');
            campaignFilter.classList.add('bg-slate-100', 'text-slate-500');
        }

        if ('BarcodeDetector' in window) {
            barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
        } else {
            setCameraStatus('BarcodeDetector is not available in this browser. Manual paste still works.');
        }

        document.getElementById('start-camera').addEventListener('click', startCamera);
        document.getElementById('stop-camera').addEventListener('click', stopCamera);
        document.getElementById('verify-payload').addEventListener('click', () => verifyPayload(qrPayload.value.trim()));
        qrImageInput.addEventListener('change', handleImageSelection);
        document.getElementById('clear-payload').addEventListener('click', () => {
            qrPayload.value = '';
            currentPayload = '';
            qrImageInput.value = '';
            checkInNote.value = '';
            clearResult();
            setCameraStatus('Payload cleared.');
        });
        campaignFilter.addEventListener('change', () => {
            if (currentPayload) {
                verifyPayload(currentPayload);
            }
        });
        todayOnly.addEventListener('change', () => {
            if (currentPayload) {
                verifyPayload(currentPayload);
            }
        });

        async function startCamera() {
            if (!barcodeDetector) {
                setCameraStatus('Camera scanning is unavailable in this browser.');
                return;
            }

            if (activeStream) {
                setCameraStatus('Camera is already running.');
                return;
            }

            try {
                activeStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });

                cameraPreview.srcObject = activeStream;
                setCameraStatus('Camera started. Point the QR code at the camera.');
                scanLoop();
            } catch (error) {
                setCameraStatus('Unable to access the camera.');
            }
        }

        function stopCamera() {
            if (scanFrame) {
                cancelAnimationFrame(scanFrame);
                scanFrame = null;
            }

            if (activeStream) {
                activeStream.getTracks().forEach((track) => track.stop());
                activeStream = null;
            }

            cameraPreview.srcObject = null;
            setCameraStatus('Camera stopped.');
        }

        async function scanLoop() {
            if (!activeStream || !barcodeDetector || isBusy) {
                scanFrame = requestAnimationFrame(scanLoop);
                return;
            }

            try {
                const barcodes = await barcodeDetector.detect(cameraPreview);
                if (barcodes.length > 0 && barcodes[0].rawValue) {
                    const payload = barcodes[0].rawValue.trim();
                    qrPayload.value = payload;
                    stopCamera();
                    await verifyPayload(payload);
                    return;
                }
            } catch (error) {
                setCameraStatus('Scanning frame failed. You can still paste the payload manually.');
            }

            scanFrame = requestAnimationFrame(scanLoop);
        }

        async function verifyPayload(payload) {
            if (!payload || isBusy) {
                return;
            }

            isBusy = true;
            currentPayload = payload;
            setResultState('verifying');
            showFeedback('Verifying signature and looking up the user...', 'info');

            try {
                const response = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        qr_payload: payload,
                        campaign_id: campaignFilter.value || null,
                        today_only: todayOnly.checked,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Verification failed.');
                }

                renderResult(data);
                showFeedback(
                    data.duplicate_warning
                        ? 'Identity verified. This user was checked in recently, so please confirm before scanning again.'
                        : 'Identity verified. You can now check in an eligible booking.',
                    data.duplicate_warning ? 'warning' : 'success'
                );
            } catch (error) {
                clearResult();
                showFeedback(error.message || 'Verification failed.', 'error');
                setResultState('error');
            } finally {
                isBusy = false;
            }
        }

        async function checkIn(bookingId) {
            if (!currentPayload || isBusy) {
                return;
            }

            isBusy = true;
            showFeedback('Recording check-in...', 'info');

            try {
                const response = await fetch(checkInUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        qr_payload: currentPayload,
                        booking_id: bookingId,
                        check_in_note: checkInNote.value.trim() || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Check-in failed.');
                }

                showFeedback(data.message, 'success');
                renderRecentScans(data.recent_scans || []);
                checkInNote.value = '';
                await verifyPayload(currentPayload);
            } catch (error) {
                showFeedback(error.message || 'Check-in failed.', 'error');
            } finally {
                isBusy = false;
            }
        }

        function renderResult(data) {
            identityCard.classList.remove('hidden');
            identityName.textContent = data.user.name;
            identityType.textContent = `${data.user.identity_label} • ${data.user.person_type}`;
            identityValue.textContent = data.user.identity_value;
            bookingCount.textContent = `${data.bookings.length} item${data.bookings.length === 1 ? '' : 's'}`;
            renderDuplicateWarning(data.duplicate_warning);
            renderRecentScans(data.recent_scans || []);

            if (data.bookings.length === 0) {
                bookingList.innerHTML = `
                    <div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                        This user has no pending, confirmed, or attended bookings in the current clinic.
                    </div>
                `;
            } else {
                bookingList.innerHTML = data.bookings.map((booking) => `
                    <div class="rounded-3xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">${escapeHtml(booking.campaign_title)}</p>
                                <p class="mt-1 text-sm text-slate-500">${escapeHtml(booking.slot_label)}</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-[0.2em] ${booking.status === 'attended' ? 'text-emerald-600' : 'text-slate-400'}">
                                    ${escapeHtml(booking.status)}
                                </p>
                            </div>
                            ${booking.can_check_in ? `
                                <button type="button" data-booking-id="${booking.id}" class="check-in-button rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                                    Check in
                                </button>
                            ` : `
                                <span class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-400">
                                    ${booking.status === 'attended' ? 'Checked in' : 'Locked'}
                                </span>
                            `}
                        </div>
                    </div>
                `).join('');

                document.querySelectorAll('.check-in-button').forEach((button) => {
                    button.addEventListener('click', () => checkIn(button.dataset.bookingId));
                });
            }

            setResultState('ready');
        }

        function clearResult() {
            identityCard.classList.add('hidden');
            duplicateWarning.classList.add('hidden');
            feedback.classList.add('hidden');
            setResultState('waiting');
            bookingCount.textContent = '0 items';
            bookingList.innerHTML = `
                <div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                    No identity has been verified yet.
                </div>
            `;
        }

        async function handleImageSelection(event) {
            const [file] = event.target.files;
            if (!file) {
                return;
            }

            if (!barcodeDetector) {
                showFeedback('This browser cannot decode QR images automatically. Paste the payload manually instead.', 'error');
                event.target.value = '';
                return;
            }

            try {
                showFeedback('Reading QR image...', 'info');
                const bitmap = await createImageBitmap(file);
                const barcodes = await barcodeDetector.detect(bitmap);
                bitmap.close();

                if (!barcodes.length || !barcodes[0].rawValue) {
                    throw new Error('No QR code was found in the selected image.');
                }

                const payload = barcodes[0].rawValue.trim();
                qrPayload.value = payload;
                await verifyPayload(payload);
            } catch (error) {
                clearResult();
                showFeedback(error.message || 'Image scan failed.', 'error');
                setResultState('error');
            } finally {
                event.target.value = '';
            }
        }

        function showFeedback(message, type) {
            const classes = {
                info: 'border-slate-200 bg-slate-50 text-slate-600',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                warning: 'border-amber-200 bg-amber-50 text-amber-800',
                error: 'border-red-200 bg-red-50 text-red-700',
            };

            feedback.className = `mt-4 rounded-2xl border px-4 py-3 text-sm font-semibold ${classes[type] || classes.info}`;
            feedback.textContent = message;
            feedback.classList.remove('hidden');
        }

        function setResultState(state) {
            const config = {
                waiting: ['Waiting', 'bg-slate-100 text-slate-400'],
                verifying: ['Verifying', 'bg-amber-100 text-amber-700'],
                ready: ['Verified', 'bg-emerald-100 text-emerald-700'],
                error: ['Error', 'bg-red-100 text-red-700'],
            };

            const [label, classes] = config[state] || config.waiting;
            resultPill.textContent = label;
            resultPill.className = `rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] ${classes}`;
        }

        function setCameraStatus(message) {
            cameraStatus.textContent = message;
        }

        function renderDuplicateWarning(warning) {
            if (!warning) {
                duplicateWarning.classList.add('hidden');
                duplicateWarningText.textContent = '';
                return;
            }

            duplicateWarningText.textContent = `${warning.message} ${warning.relative_time} • ${warning.campaign_title} (${warning.booking_code})`;
            duplicateWarning.classList.remove('hidden');
        }

        function renderRecentScans(scans) {
            recentScanCount.textContent = `${scans.length} item${scans.length === 1 ? '' : 's'}`;

            if (scans.length === 0) {
                recentScanList.innerHTML = `
                    <div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                        No recent check-ins yet.
                    </div>
                `;
                return;
            }

            recentScanList.innerHTML = scans.map((scan) => `
                <div class="rounded-3xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">${escapeHtml(scan.user_name)}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">${escapeHtml(scan.identity_label)}: ${escapeHtml(scan.identity_value)}</p>
                            <p class="mt-2 text-sm text-slate-500">${escapeHtml(scan.campaign_title)}</p>
                            <p class="mt-1 text-xs text-slate-400">${escapeHtml(scan.slot_label)}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">${escapeHtml(scan.relative_time || '-')}</p>
                            <p class="mt-2 text-xs text-slate-400">${escapeHtml(scan.staff_name || 'Staff')}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</body>
</html>
