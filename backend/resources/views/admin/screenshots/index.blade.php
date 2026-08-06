@extends('layouts.admin')

@push('styles')
    <style>
        .screenshot-compare {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .screenshot-card {
            display: grid;
            gap: 12px;
            padding: 14px;
            border-radius: 22px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.94));
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .screenshot-card__preview {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);
            cursor: zoom-in;
        }

        .screenshot-card__meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .screenshot-card__meta strong {
            display: block;
            margin-bottom: 4px;
        }

        .screenshot-trigger {
            cursor: zoom-in;
        }

        .viewer-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            place-items: center;
            padding: 18px;
            background: rgba(2, 6, 23, 0.84);
            backdrop-filter: blur(14px);
        }

        .viewer-modal.is-open {
            display: grid;
        }

        .viewer-modal__panel {
            width: min(96vw, 1540px);
            height: min(94vh, 980px);
            display: grid;
            grid-template-rows: auto 1fr;
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
            box-shadow: 0 40px 120px rgba(0, 0, 0, 0.35);
        }

        .viewer-modal__toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255,255,255,0.88);
        }

        .viewer-modal__title {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 18px;
        }

        .viewer-modal__meta {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
        }

        .viewer-modal__controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .viewer-modal__zoom {
            width: 180px;
        }

        .viewer-modal__content {
            position: relative;
            min-height: 0;
            overflow: auto;
            padding: 18px;
        }

        .viewer-modal__single,
        .viewer-modal__compare {
            min-height: 100%;
            display: none;
        }

        .viewer-modal__single.is-active,
        .viewer-modal__compare.is-active {
            display: grid;
        }

        .viewer-modal__single {
            place-items: center;
        }

        .viewer-modal__single img {
            max-width: 100%;
            max-height: calc(94vh - 130px);
            transform-origin: center center;
            transition: transform 140ms ease;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.30);
        }

        .viewer-modal__compare {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            align-items: start;
        }

        .viewer-modal__compare figure {
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .viewer-modal__compare img {
            width: 100%;
            max-height: calc(94vh - 180px);
            object-fit: contain;
            transform-origin: center center;
            transition: transform 140ms ease;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
            background: white;
        }

        .viewer-modal__compare figcaption {
            color: var(--muted);
            font-size: 13px;
        }

        body.modal-open {
            overflow: hidden;
        }

        @media (max-width: 900px) {
            .viewer-modal__panel {
                width: 100%;
                height: 100%;
                border-radius: 18px;
            }

            .viewer-modal__toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .viewer-modal__compare {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card soft" style="margin-bottom:18px;">
        <div class="section-title">
            <h3>Screenshot Monitoring</h3>
            <span class="badge">{{ $screenshots->total() }} records</span>
        </div>
        <p class="muted">Filter screenshots by employee device or date, then open a preview instantly.</p>

        <form method="GET" class="form-row">
            <div>
                <label for="device_id">Employee / Device</label>
                <select id="device_id" name="device_id">
                    <option value="">All devices</option>
                    @foreach ($devices as $device)
                        <option value="{{ $device->device_id }}" @selected(request('device_id') === $device->device_id)>
                            {{ $device->employee_name }} ({{ $device->device_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date">Date</label>
                <input id="date" type="date" name="date" value="{{ request('date') }}">
            </div>
            <div style="align-self:end;">
                <button type="submit" class="btn primary" style="width:100%;">Filter</button>
            </div>
        </form>
    </div>

    @if ($compareScreenshots->isNotEmpty())
        <div class="card soft" style="margin-bottom:18px;">
            <div class="section-title">
                <h3>Quick Compare</h3>
                <span class="badge">Latest captures</span>
            </div>
            <div class="screenshot-compare">
                @foreach ($compareScreenshots as $screenshot)
                    <article class="screenshot-card">
                        <img
                            class="screenshot-card__preview screenshot-trigger"
                            loading="lazy"
                            src="{{ asset('storage/' . $screenshot->image_path) }}"
                            alt="Screenshot preview"
                            data-screenshot-trigger
                            data-screenshot-mode="single"
                            data-screenshot-url="{{ asset('storage/' . $screenshot->image_path) }}"
                            data-screenshot-title="{{ $screenshot->device?->employee_name ?? 'Unknown' }}"
                            data-screenshot-meta="{{ $screenshot->created_at->format('M d, Y h:i A') }} - {{ $screenshot->device?->device_id ?? 'Unknown device' }}"
                        >
                        <div class="screenshot-card__meta">
                            <div>
                                <strong>{{ $screenshot->device?->employee_name ?? 'Unknown' }}</strong>
                                <div class="muted">{{ $screenshot->created_at->format('M d, Y h:i A') }}</div>
                                <div class="muted">{{ $screenshot->device?->device_id ?? 'Unknown device' }}</div>
                            </div>
                            <button
                                type="button"
                                class="btn primary"
                                data-screenshot-trigger
                                data-screenshot-mode="single"
                                data-screenshot-url="{{ asset('storage/' . $screenshot->image_path) }}"
                                data-screenshot-title="{{ $screenshot->device?->employee_name ?? 'Unknown' }}"
                                data-screenshot-meta="{{ $screenshot->created_at->format('M d, Y h:i A') }} - {{ $screenshot->device?->device_id ?? 'Unknown device' }}"
                            >
                                Open fullscreen
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($compareScreenshots->count() > 1)
                <div class="actions" style="margin-top:14px;">
                    <button
                        type="button"
                        class="btn primary"
                        data-screenshot-compare-open
                    >
                        Compare latest two
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="card soft">
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Employee</th>
                    <th>Device</th>
                    <th>Captured At</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($screenshots as $screenshot)
                    <tr>
                        <td>
                            <img
                                class="preview screenshot-trigger"
                                loading="lazy"
                                src="{{ asset('storage/' . $screenshot->image_path) }}"
                                alt="Screenshot preview"
                                data-screenshot-trigger
                                data-screenshot-mode="single"
                                data-screenshot-url="{{ asset('storage/' . $screenshot->image_path) }}"
                                data-screenshot-title="{{ $screenshot->device?->employee_name ?? 'Unknown' }}"
                                data-screenshot-meta="{{ $screenshot->created_at->format('M d, Y h:i A') }} - {{ $screenshot->device?->device_id ?? 'Unknown device' }}"
                            >
                        </td>
                        <td>{{ $screenshot->device?->employee_name ?? 'Unknown' }}</td>
                        <td>{{ $screenshot->device?->device_id ?? 'Unknown' }}</td>
                        <td>{{ $screenshot->created_at->format('M d, Y h:i A') }}</td>
                        <td><code>{{ $screenshot->image_path }}</code></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">No screenshots found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $screenshots->links('components.pagination.compact') }}
        </div>
    </div>

    <div class="viewer-modal" data-screenshot-modal aria-hidden="true">
        <div class="viewer-modal__panel" role="dialog" aria-modal="true" aria-labelledby="screenshotViewerTitle">
            <div class="viewer-modal__toolbar">
                <div>
                    <h3 class="viewer-modal__title" id="screenshotViewerTitle" data-screenshot-modal-title>Screenshot preview</h3>
                    <div class="viewer-modal__meta" data-screenshot-modal-meta>Open a capture to inspect it closely.</div>
                </div>
                <div class="viewer-modal__controls">
                    <label class="muted" for="screenshotZoom" style="margin:0;">Zoom</label>
                    <input class="viewer-modal__zoom" id="screenshotZoom" type="range" min="1" max="3" step="0.1" value="1" data-screenshot-zoom>
                    <button type="button" class="btn" data-screenshot-close>Close</button>
                </div>
            </div>

            <div class="viewer-modal__content">
                <div class="viewer-modal__single is-active" data-screenshot-single-view>
                    <img src="" alt="Fullscreen screenshot" data-screenshot-modal-image>
                </div>

                <div class="viewer-modal__compare" data-screenshot-compare-view>
                    <figure>
                        <img src="" alt="Latest screenshot" data-screenshot-compare-left>
                        <figcaption data-screenshot-compare-left-meta></figcaption>
                    </figure>
                    <figure>
                        <img src="" alt="Previous screenshot" data-screenshot-compare-right>
                        <figcaption data-screenshot-compare-right-meta></figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const modal = document.querySelector('[data-screenshot-modal]');
            if (! modal) {
                return;
            }

            const closeButtons = modal.querySelectorAll('[data-screenshot-close]');
            const singleView = modal.querySelector('[data-screenshot-single-view]');
            const compareView = modal.querySelector('[data-screenshot-compare-view]');
            const singleImage = modal.querySelector('[data-screenshot-modal-image]');
            const titleNode = modal.querySelector('[data-screenshot-modal-title]');
            const metaNode = modal.querySelector('[data-screenshot-modal-meta]');
            const zoomInput = modal.querySelector('[data-screenshot-zoom]');
            const compareLeft = modal.querySelector('[data-screenshot-compare-left]');
            const compareRight = modal.querySelector('[data-screenshot-compare-right]');
            const compareLeftMeta = modal.querySelector('[data-screenshot-compare-left-meta]');
            const compareRightMeta = modal.querySelector('[data-screenshot-compare-right-meta]');
            const compareOpen = document.querySelector('[data-screenshot-compare-open]');
            const compareCards = @json($compareScreenshotsPayload);


            let currentScale = 1;

            function setModalOpen(isOpen) {
                modal.classList.toggle('is-open', isOpen);
                modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                document.body.classList.toggle('modal-open', isOpen);
            }

            function setScale(scale) {
                currentScale = Math.min(3, Math.max(1, scale));
                if (singleImage) {
                    singleImage.style.transform = `scale(${currentScale})`;
                }
                [compareLeft, compareRight].forEach((img) => {
                    if (img) {
                        img.style.transform = `scale(${currentScale})`;
                    }
                });
                if (zoomInput) {
                    zoomInput.value = String(currentScale);
                }
            }

            function showSingle(item) {
                if (! item || ! singleView || ! compareView) {
                    return;
                }

                singleView.classList.add('is-active');
                compareView.classList.remove('is-active');
                if (singleImage) {
                    singleImage.src = item.url;
                }
                if (titleNode) {
                    titleNode.textContent = item.title || 'Screenshot preview';
                }
                if (metaNode) {
                    metaNode.textContent = item.meta || '';
                }
                setScale(1);
                setModalOpen(true);
            }

            function showCompare() {
                if (! compareCards.length || ! compareView || ! singleView) {
                    return;
                }

                singleView.classList.remove('is-active');
                compareView.classList.add('is-active');

                const [latest, previous] = compareCards;
                if (compareLeft) {
                    compareLeft.src = latest?.url || '';
                }
                if (compareRight) {
                    compareRight.src = previous?.url || latest?.url || '';
                }
                if (compareLeftMeta) {
                    compareLeftMeta.textContent = latest?.title ? `${latest.title} - ${latest.meta}` : '';
                }
                if (compareRightMeta) {
                    compareRightMeta.textContent = previous?.title ? `${previous.title} - ${previous.meta}` : '';
                }
                if (titleNode) {
                    titleNode.textContent = 'Compare latest screenshots';
                }
                if (metaNode) {
                    metaNode.textContent = 'Side-by-side comparison of the latest available captures.';
                }
                setScale(1);
                setModalOpen(true);
            }

            document.querySelectorAll('[data-screenshot-trigger]').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    showSingle({
                        url: trigger.getAttribute('data-screenshot-url'),
                        title: trigger.getAttribute('data-screenshot-title'),
                        meta: trigger.getAttribute('data-screenshot-meta'),
                    });
                });
            });

            if (compareOpen) {
                compareOpen.addEventListener('click', showCompare);
            }

            if (zoomInput) {
                zoomInput.addEventListener('input', () => setScale(parseFloat(zoomInput.value)));
            }

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => setModalOpen(false));
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    setModalOpen(false);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setModalOpen(false);
                }
            });
        })();
    </script>
@endpush


