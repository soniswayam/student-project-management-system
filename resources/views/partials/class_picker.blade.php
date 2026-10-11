@php
    $firstDept = $picker['departments'][0]['id'] ?? null;
    $firstOptions = $picker['semesters'][$firstDept] ?? [];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card mt-lg-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="stat-ico tone-primary"><i class="bi bi-mortarboard"></i></span>
                    <div>
                        <h5 class="mb-1">Select a class</h5>
                        <p class="text-muted small mb-0">Choose department and semester to see its {{ $noun }}.</p>
                    </div>
                </div>

                <form method="GET" action="{{ $action }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pickDept" class="form-label">Department</label>
                            <select id="pickDept" name="department_id" class="form-select">
                                @foreach($picker['departments'] as $dept)
                                    <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pickSem" class="form-label">Semester</label>
                            <select id="pickSem" name="semester" class="form-select">
                                @foreach($firstOptions as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center small mt-3" id="pickChips">
                        <span class="text-muted">In this class:</span>
                        @foreach(($firstOptions[0]['chips'] ?? []) as $i => $chip)
                            <span class="badge bg-{{ ['primary', 'info', 'warning'][$i] ?? 'secondary' }}">{{ $chip }}</span>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="{{ $allUrl }}" class="small">Show all my {{ $noun }}</a>
                        <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Show {{ $noun }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var data = @json($picker['semesters']);
    var dept = document.getElementById('pickDept');
    var sem = document.getElementById('pickSem');
    var chipBox = document.getElementById('pickChips');
    if (!dept || !sem || !chipBox) return;

    var tones = ['primary', 'info', 'warning'];

    function current() {
        var options = data[dept.value] || [];
        return options.find(function (o) { return o.value === sem.value; }) || options[0];
    }

    function renderChips() {
        var opt = current();
        chipBox.innerHTML = '';
        var lead = document.createElement('span');
        lead.className = 'text-muted';
        lead.textContent = 'In this class:';
        chipBox.appendChild(lead);
        (opt ? opt.chips : []).forEach(function (text, i) {
            var chip = document.createElement('span');
            chip.className = 'badge bg-' + (tones[i] || 'secondary');
            chip.textContent = text;
            chipBox.appendChild(chip);
        });
    }

    function renderSemesters() {
        sem.innerHTML = '';
        (data[dept.value] || []).forEach(function (o) {
            sem.add(new Option(o.label, o.value));
        });
        renderChips();
    }

    dept.addEventListener('change', renderSemesters);
    sem.addEventListener('change', renderChips);
})();
</script>
@endpush
