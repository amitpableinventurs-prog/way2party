@extends('scanner.layout')
@section('title', $event->name)
@section('head')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection
@section('body')
    <div class="topbar">
        <a href="{{ route('scannerPanel.events') }}">&larr; {{ __('Events') }}</a>
        <h1 style="font-size:14px;">{{ \Illuminate\Support\Str::limit($event->name, 28) }}</h1>
        <a href="{{ route('scannerPanel.logout') }}">{{ __('Logout') }}</a>
    </div>
    <div class="container">
        <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="muted">{{ __('Checked in') }}</div>
                <div id="checkedInCount" style="font-size:22px; font-weight:700;">{{ $event->checked_in_count }}/{{ $event->total_tickets }}</div>
            </div>
            <div style="text-align:right;">
                <div class="muted">{{ \Carbon\Carbon::parse($event->start_time)->format('d M Y') }}</div>
                <a href="{{ route('scannerPanel.history', $event->id) }}" class="muted">{{ __('Full history') }} &rarr;</a>
            </div>
        </div>

        <div class="card">
            <div id="scanResult" style="display:none; margin-bottom: 14px; padding: 14px; border-radius: 10px;"></div>

            <div id="reader" style="width:100%; border-radius: 10px; overflow:hidden;"></div>
            <p class="muted" style="text-align:center; margin-top:8px;">{{ __('Point the camera at the ticket QR code') }}</p>

            <hr style="border:none; border-top:1px solid rgba(255,255,255,0.08); margin: 16px 0;">

            <label for="manualCode">{{ __('Or enter the ticket code manually') }}</label>
            <form id="manualForm" style="display:flex; gap:8px;">
                <input type="text" id="manualCode" placeholder="{{ __('Ticket code') }}" style="margin-bottom:0;" autocomplete="off">
                <button type="submit" class="btn" style="width:auto; white-space:nowrap;">{{ __('Check') }}</button>
            </form>
        </div>

        <div class="card">
            <div style="font-weight:600; margin-bottom: 10px;">{{ __('Recent Scans') }}</div>
            <table id="historyTable">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Result') }}</th>
                        <th>{{ __('Time') }}</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    @forelse ($recentScans as $scan)
                        <tr>
                            <td>{{ $scan->ticket_number }}</td>
                            <td><span class="badge {{ $scan->result }}">{{ ucfirst(str_replace('_', ' ', $scan->result)) }}</span></td>
                            <td class="muted">{{ $scan->created_at->format('h:i a') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">{{ __('No scans yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const scanUrl = @json(route('scannerPanel.scan', $event->id));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const resultBox = document.getElementById('scanResult');
        const historyBody = document.getElementById('historyBody');
        const checkedInCount = document.getElementById('checkedInCount');

        const resultStyles = {
            success: { bg: 'rgba(34,197,94,0.15)', color: '#86efac', label: @json(__('Checked In')) },
            already_used: { bg: 'rgba(245,158,11,0.15)', color: '#fcd34d', label: @json(__('Already Used')) },
            cancelled: { bg: 'rgba(148,163,184,0.15)', color: '#cbd5e1', label: @json(__('Cancelled')) },
            invalid: { bg: 'rgba(239,68,68,0.15)', color: '#fca5a5', label: @json(__('Invalid')) },
            expired: { bg: 'rgba(239,68,68,0.15)', color: '#fca5a5', label: @json(__('Expired')) },
        };

        let scanning = true;

        function showResult(data, code) {
            const style = resultStyles[data.status] || resultStyles.invalid;
            resultBox.style.display = 'block';
            resultBox.style.background = style.bg;
            resultBox.style.color = style.color;
            let html = '<div style="font-weight:700; font-size:16px;">' + style.label + '</div>';
            html += '<div style="margin-top:4px;">' + (data.message || '') + '</div>';
            if (data.status === 'success') {
                html += '<div style="margin-top:8px; font-size:13px;">';
                if (data.holder_name) html += '<div>' + @json(__('Guest')) + ': ' + data.holder_name + '</div>';
                if (data.ticket_type) html += '<div>' + @json(__('Ticket')) + ': ' + data.ticket_type + '</div>';
                if (data.remaining_check_ins !== undefined) html += '<div>' + @json(__('Remaining check-ins')) + ': ' + data.remaining_check_ins + '</div>';
                html += '</div>';
            }
            resultBox.innerHTML = html;

            const row = document.createElement('tr');
            row.innerHTML = '<td>' + (code || '') + '</td>' +
                '<td><span class="badge ' + data.status + '">' + style.label + '</span></td>' +
                '<td class="muted">' + new Date().toLocaleTimeString() + '</td>';
            if (historyBody.firstElementChild && historyBody.firstElementChild.children.length === 3 && historyBody.children.length === 1 && historyBody.firstElementChild.querySelector('[colspan]')) {
                historyBody.innerHTML = '';
            }
            historyBody.prepend(row);
        }

        function submitCode(code) {
            if (!code) return;
            fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code: code }),
            })
                .then(res => res.json())
                .then(data => {
                    showResult(data, code);
                    if (data.status === 'success') {
                        const parts = checkedInCount.textContent.split('/');
                        const total = parts[1] || '0';
                        checkedInCount.textContent = (parseInt(parts[0] || '0') + 1) + '/' + total;
                    }
                })
                .catch(() => {
                    showResult({ status: 'invalid', message: @json(__('Network error. Please try again.')) }, code);
                });
        }

        document.getElementById('manualForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('manualCode');
            submitCode(input.value.trim());
            input.value = '';
        });

        if (window.Html5Qrcode) {
            const html5QrCode = new Html5Qrcode('reader');
            const config = { fps: 10, qrbox: { width: 220, height: 220 } };
            let lastScan = '';
            let lastScanTime = 0;

            Html5Qrcode.getCameras().then(cameras => {
                if (!cameras || !cameras.length) return;
                const cameraId = cameras.find(c => /back|rear|environment/i.test(c.label))?.id || cameras[cameras.length - 1].id;
                html5QrCode.start(
                    cameraId,
                    config,
                    (decodedText) => {
                        const now = Date.now();
                        if (decodedText === lastScan && (now - lastScanTime) < 3000) return;
                        lastScan = decodedText;
                        lastScanTime = now;
                        submitCode(decodedText);
                    },
                    () => {}
                ).catch(() => {
                    document.getElementById('reader').innerHTML = '<p class="muted" style="padding:20px; text-align:center;">' + @json(__('Camera unavailable. Use manual entry below.')) + '</p>';
                });
            }).catch(() => {
                document.getElementById('reader').innerHTML = '<p class="muted" style="padding:20px; text-align:center;">' + @json(__('Camera access denied. Use manual entry below.')) + '</p>';
            });
        } else {
            document.getElementById('reader').innerHTML = '<p class="muted" style="padding:20px; text-align:center;">' + @json(__('Camera scanner failed to load. Use manual entry below.')) + '</p>';
        }
    </script>
@endsection
