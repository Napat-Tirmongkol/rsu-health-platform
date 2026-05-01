<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Scanner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- jsQR: cross-browser QR decoder (Chrome, Safari, Firefox, iOS, Android) --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
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

            {{-- Left column: Scanner --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Camera Scanner</h2>
                        <p class="text-sm text-slate-500">Works on all browsers including iOS Safari and Firefox.</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="start-camera" type="button"
                            class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700">
                            Start camera
                        </button>
                        <button id="stop-camera" type="button"
                            class="rounded-2xl bg-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-300">
                            Stop
                        </button>
                    </div>
                </div>

                {{-- Camera preview + hidden decode canvas --}}
                <div class="relative mt-4 overflow-hidden rounded-3xl border border-slate-200 bg-slate-950">
                    <video id="camera-preview" autoplay muted playsinline
                        class="aspect-[4/3] w-full object-cover"></video>
                    {{-- Scan overlay --}}
                    <div id="scan-overlay" class="pointer-events-none absolute inset-0 hidden items-center justify-center">
                        <div class="h-48 w-48 rounded-2xl border-4 border-emerald-400 opacity-70 shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]"></div>
                    </div>
                </div>
                <canvas id="decode-canvas" class="hidden"></canvas>

                <p id="camera-status" class="mt-4 text-sm font-semibold text-slate-500">
                    Camera is idle. Point QR code at the camera or paste / upload below.
                </p>

                <div class="mt-6">
                    <label for="qr-payload" class="mb-2 block text-sm font-bold text-slate-700">QR payload (manual)</label>
                    <textarea id="qr-payload" rows="6"
                        class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        placeholder="Paste raw QR payload here…"></textarea>
                </div>

                <div class="mt-4">
                    <label for="qr-image" class="mb-2 block text-sm font-bold text-slate-700">Upload QR image</label>
                    <label for="qr-image"
                        class="flex cursor-pointer items-center justify-center gap-3 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50">
                        <span>Choose an image with a QR code</span>
                    </label>
                    <input id="qr-image" type="file" accept="image/*" class="hidden">
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button id="verify-payload" type="button"
                        class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800">
                        Verify identity
                    </button>
                    <button id="clear-payload" type="button"
                        class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                        Clear
                    </button>
                </div>
            </section>

            {{-- Right column: Result --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Verification Result</h2>
                        <p class="text-sm text-slate-500">Only pending or confirmed bookings can be checked in.</p>
                    </div>
                    <div id="result-pill"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Waiting
                    </div>
                </div>

                <div id="feedback" class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm font-semibold"></div>

                <div class="mt-4">
                    <label for="check-in-note" class="mb-2 block text-sm font-bold text-slate-700">Check-in note</label>
                    <textarea id="check-in-note" rows="3"
                        class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        placeholder="Optional note for exceptions, walk-in context, or staff remarks"></textarea>
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

                <div id="duplicate-warning"
                    class="mt-4 hidden rounded-3xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-700">Duplicate warning</p>
                    <p id="duplicate-warning-text" class="mt-2 text-sm font-semibold text-amber-900"></p>
                </div>

                <div class="mt-6">
                    <div class="mb-4 grid gap-3 rounded-3xl bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <label for="campaign-filter"
                                class="mb-2 block text-xs font-black uppercase tracking-[0.2em] text-slate-500">Campaign filter</label>
                            <select id="campaign-filter"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
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
                            <input id="today-only" type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-slate-700">Today only</span>
                        </label>
                    </div>

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-700">Bookings</h3>
                        <span id="booking-count" class="text-xs font-bold text-slate-400">0 items</span>
                    </div>

                    <div id="booking-list" class="space-y-3">
                        <div
                            class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                            No identity has been verified yet.
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-700">Recent scans</h3>
                        <span id="recent-scan-count"
                            class="text-xs font-bold text-slate-400">{{ count($recentScans) }} items</span>
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
                            <div
                                class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                                No recent check-ins yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const verifyUrl   = @json(route('staff.scan.verify'));
        const checkInUrl  = @json(route('staff.scan.check-in'));
        const csrfToken   = @json(csrf_token());
        const boundCampaign = @json($boundCampaign);

        // ── DOM refs ──────────────────────────────────────────────────────────
        const cameraPreview      = document.getElementById('camera-preview');
        const decodeCanvas       = document.getElementById('decode-canvas');
        const scanOverlay        = document.getElementById('scan-overlay');
        const cameraStatus       = document.getElementById('camera-status');
        const qrPayload          = document.getElementById('qr-payload');
        const qrImageInput       = document.getElementById('qr-image');
        const campaignFilter     = document.getElementById('campaign-filter');
        const todayOnly          = document.getElementById('today-only');
        const feedback           = document.getElementById('feedback');
        const checkInNote        = document.getElementById('check-in-note');
        const resultPill         = document.getElementById('result-pill');
        const identityCard       = document.getElementById('identity-card');
        const duplicateWarning   = document.getElementById('duplicate-warning');
        const duplicateWarningText = document.getElementById('duplicate-warning-text');
        const identityName       = document.getElementById('identity-name');
        const identityType       = document.getElementById('identity-type');
        const identityValue      = document.getElementById('identity-value');
        const bookingCount       = document.getElementById('booking-count');
        const bookingList        = document.getElementById('booking-list');
        const recentScanCount    = document.getElementById('recent-scan-count');
        const recentScanList     = document.getElementById('recent-scan-list');
        const ctx                = decodeCanvas.getContext('2d', { willReadFrequently: true });

        let activeStream   = null;
        let scanFrame      = null;
        let isBusy         = false;
        let currentPayload = '';
        let lastScannedRaw = '';   // debounce: skip identical frames

        if (boundCampaign) {
            campaignFilter.value = String(boundCampaign.id);
            campaignFilter.setAttribute('disabled', 'disabled');
            campaignFilter.classList.add('bg-slate-100', 'text-slate-500');
        }

        // ── Event listeners ───────────────────────────────────────────────────
        document.getElementById('start-camera').addEventListener('click', startCamera);
        document.getElementById('stop-camera').addEventListener('click', stopCamera);
        document.getElementById('verify-payload').addEventListener('click', () => verifyPayload(qrPayload.value.trim()));
        qrImageInput.addEventListener('change', handleImageUpload);
        document.getElementById('clear-payload').addEventListener('click', () => {
            qrPayload.value = '';
            currentPayload = '';
            lastScannedRaw = '';
            qrImageInput.value = '';
            checkInNote.value = '';
            clearResult();
            setCameraStatus('Payload cleared.');
        });
        campaignFilter.addEventListener('change', () => { if (currentPayload) verifyPayload(currentPayload); });
        todayOnly.addEventListener('change',       () => { if (currentPayload) verifyPayload(currentPayload); });

        // ── Camera ────────────────────────────────────────────────────────────
        async function startCamera() {
            if (activeStream) { setCameraStatus('Camera is already running.'); return; }

            try {
                activeStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } },
                    audio: false,
                });
                cameraPreview.srcObject = activeStream;
                await cameraPreview.play();
                scanOverlay.classList.remove('hidden');
                scanOverlay.classList.add('flex');
                setCameraStatus('Camera started — point the QR code at the viewfinder.');
                scanLoop();
            } catch (err) {
                setCameraStatus('Cannot access camera: ' + (err.message || 'permission denied.'));
            }
        }

        function stopCamera() {
            if (scanFrame) { cancelAnimationFrame(scanFrame); scanFrame = null; }
            if (activeStream) { activeStream.getTracks().forEach(t => t.stop()); activeStream = null; }
            cameraPreview.srcObject = null;
            scanOverlay.classList.add('hidden');
            scanOverlay.classList.remove('flex');
            setCameraStatus('Camera stopped.');
        }

        // jsQR decode loop — works on all browsers (Chrome, Safari, Firefox, iOS, Android)
        function scanLoop() {
            if (!activeStream) return;

            if (
                !isBusy &&
                cameraPreview.readyState === cameraPreview.HAVE_ENOUGH_DATA &&
                cameraPreview.videoWidth > 0
            ) {
                decodeCanvas.width  = cameraPreview.videoWidth;
                decodeCanvas.height = cameraPreview.videoHeight;
                ctx.drawImage(cameraPreview, 0, 0, decodeCanvas.width, decodeCanvas.height);

                const imageData = ctx.getImageData(0, 0, decodeCanvas.width, decodeCanvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code && code.data && code.data !== lastScannedRaw) {
                    lastScannedRaw = code.data;
                    const payload = code.data.trim();
                    qrPayload.value = payload;
                    stopCamera();
                    verifyPayload(payload);
                    return;
                }
            }

            scanFrame = requestAnimationFrame(scanLoop);
        }

        // ── Image upload decode (jsQR, cross-browser) ─────────────────────────
        async function handleImageUpload(event) {
            const [file] = event.target.files;
            if (!file) return;

            showFeedback('Reading QR from image…', 'info');

            try {
                const bitmap = await createImageBitmap(file);
                decodeCanvas.width  = bitmap.width;
                decodeCanvas.height = bitmap.height;
                ctx.drawImage(bitmap, 0, 0);
                bitmap.close();

                const imageData = ctx.getImageData(0, 0, decodeCanvas.width, decodeCanvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'attemptBoth',
                });

                if (!code || !code.data) {
                    throw new Error('No QR code found in the selected image. Try a clearer photo.');
                }

                const payload = code.data.trim();
                qrPayload.value = payload;
                await verifyPayload(payload);
            } catch (err) {
                clearResult();
                showFeedback(err.message || 'Image decode failed.', 'error');
                setResultState('error');
            } finally {
                event.target.value = '';
            }
        }

        // ── Verify ────────────────────────────────────────────────────────────
        async function verifyPayload(payload) {
            if (!payload || isBusy) return;

            isBusy = true;
            currentPayload = payload;
            setResultState('verifying');
            showFeedback('Verifying signature and looking up the user…', 'info');

            try {
                const res  = await post(verifyUrl, {
                    qr_payload:  payload,
                    campaign_id: campaignFilter.value || null,
                    today_only:  todayOnly.checked,
                });
                const data = await res.json();

                if (!res.ok) throw new Error(data.message || 'Verification failed.');

                renderResult(data);
                showFeedback(
                    data.duplicate_warning
                        ? 'Identity verified — duplicate check-in detected, please confirm.'
                        : 'Identity verified. Select a booking to check in.',
                    data.duplicate_warning ? 'warning' : 'success'
                );
            } catch (err) {
                clearResult();
                showFeedback(err.message || 'Verification failed.', 'error');
                setResultState('error');
            } finally {
                isBusy = false;
            }
        }

        // ── Check-in ──────────────────────────────────────────────────────────
        async function checkIn(bookingId) {
            if (!currentPayload || isBusy) return;

            isBusy = true;
            showFeedback('Recording check-in…', 'info');

            try {
                const res  = await post(checkInUrl, {
                    qr_payload:    currentPayload,
                    booking_id:    bookingId,
                    check_in_note: checkInNote.value.trim() || null,
                });
                const data = await res.json();

                if (!res.ok) throw new Error(data.message || 'Check-in failed.');

                showFeedback('✓ ' + data.message, 'success');
                renderRecentScans(data.recent_scans || []);
                checkInNote.value = '';
                await verifyPayload(currentPayload);
            } catch (err) {
                showFeedback(err.message || 'Check-in failed.', 'error');
            } finally {
                isBusy = false;
            }
        }

        // ── Render helpers ────────────────────────────────────────────────────
        function renderResult(data) {
            identityCard.classList.remove('hidden');
            identityName.textContent  = data.user.name;
            identityType.textContent  = `${data.user.identity_label} · ${data.user.person_type}`;
            identityValue.textContent = data.user.identity_value;
            bookingCount.textContent  = `${data.bookings.length} item${data.bookings.length === 1 ? '' : 's'}`;

            renderDuplicateWarning(data.duplicate_warning);
            renderRecentScans(data.recent_scans || []);

            bookingList.innerHTML = data.bookings.length === 0
                ? `<div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                       This user has no pending, confirmed, or attended bookings.
                   </div>`
                : data.bookings.map(b => `
                    <div class="rounded-3xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">${esc(b.campaign_title)}</p>
                                <p class="mt-1 text-sm text-slate-500">${esc(b.slot_label)}</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-[0.2em] ${b.status === 'attended' ? 'text-emerald-600' : 'text-slate-400'}">
                                    ${esc(b.status)}
                                </p>
                            </div>
                            ${b.can_check_in
                                ? `<button type="button" data-booking-id="${b.id}"
                                       class="check-in-button rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                                       Check in
                                   </button>`
                                : `<span class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-400">
                                       ${b.status === 'attended' ? 'Checked in' : 'Locked'}
                                   </span>`
                            }
                        </div>
                    </div>`
                ).join('');

            bookingList.querySelectorAll('.check-in-button').forEach(btn => {
                btn.addEventListener('click', () => checkIn(btn.dataset.bookingId));
            });

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
                </div>`;
        }

        function renderDuplicateWarning(warning) {
            if (!warning) { duplicateWarning.classList.add('hidden'); return; }
            duplicateWarningText.textContent =
                `${warning.message} ${warning.relative_time} · ${warning.campaign_title} (${warning.booking_code})`;
            duplicateWarning.classList.remove('hidden');
        }

        function renderRecentScans(scans) {
            recentScanCount.textContent = `${scans.length} item${scans.length === 1 ? '' : 's'}`;
            recentScanList.innerHTML = scans.length === 0
                ? `<div class="rounded-3xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">No recent check-ins yet.</div>`
                : scans.map(s => `
                    <div class="rounded-3xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">${esc(s.user_name)}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">${esc(s.identity_label)}: ${esc(s.identity_value)}</p>
                                <p class="mt-2 text-sm text-slate-500">${esc(s.campaign_title)}</p>
                                <p class="mt-1 text-xs text-slate-400">${esc(s.slot_label)}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">${esc(s.relative_time || '-')}</p>
                                <p class="mt-2 text-xs text-slate-400">${esc(s.staff_name || 'Staff')}</p>
                            </div>
                        </div>
                    </div>`
                ).join('');
        }

        // ── UI state ──────────────────────────────────────────────────────────
        function showFeedback(message, type) {
            const map = {
                info:    'border-slate-200 bg-slate-50 text-slate-600',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                warning: 'border-amber-200 bg-amber-50 text-amber-800',
                error:   'border-red-200 bg-red-50 text-red-700',
            };
            feedback.className = `mt-4 rounded-2xl border px-4 py-3 text-sm font-semibold ${map[type] || map.info}`;
            feedback.textContent = message;
            feedback.classList.remove('hidden');
        }

        function setResultState(state) {
            const map = {
                waiting:   ['Waiting',   'bg-slate-100 text-slate-400'],
                verifying: ['Verifying', 'bg-amber-100 text-amber-700'],
                ready:     ['Verified',  'bg-emerald-100 text-emerald-700'],
                error:     ['Error',     'bg-red-100 text-red-700'],
            };
            const [label, cls] = map[state] || map.waiting;
            resultPill.textContent = label;
            resultPill.className = `rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] ${cls}`;
        }

        function setCameraStatus(msg) { cameraStatus.textContent = msg; }

        // ── Utilities ─────────────────────────────────────────────────────────
        function post(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(body),
            });
        }

        function esc(v) {
            return String(v ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    </script>
</body>
</html>
