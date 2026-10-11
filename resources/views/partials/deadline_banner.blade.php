@if($window->hasDeadline())
    <div class="alert alert-{{ $window->color() }} d-flex align-items-start gap-2">
        <i class="bi bi-{{ $window->isClosed() ? 'lock-fill' : ($window->isInGrace() ? 'exclamation-triangle-fill' : 'clock-history') }} mt-1"></i>
        <div class="flex-grow-1">
            <div class="fw-semibold">
                @if($window->isClosed())
                    Submission window closed
                @elseif($window->isInGrace())
                    Overdue — late submission still accepted
                @else
                    Due {{ $window->dueAt->format('d M Y, H:i') }}
                @endif
            </div>

            <div class="small">
                @if($window->isClosed())
                    Closed on {{ $window->closesAt()->format('d M Y, H:i') }}
                    ({{ $window->graceHours }}h after the due date). Contact your department if you need an extension.
                @elseif($window->isInGrace())
                    The due date was {{ $window->dueAt->format('d M Y, H:i') }}.
                    You can still submit for <strong>{{ $window->timeLeftForHumans() }}</strong>
                    (until {{ $window->closesAt()->format('d M Y, H:i') }}), but it will be
                    recorded as a <strong>late submission</strong>.
                @else
                    <strong>{{ $window->timeLeftForHumans() }}</strong> left.
                    After the due date you have a {{ $window->graceHours }}-hour grace period —
                    submissions in that window are accepted but marked late.
                @endif

                @isset($sourceNote)
                    <span class="d-block text-muted mt-1">{{ $sourceNote }}</span>
                @endisset
            </div>
        </div>
    </div>
@endif
