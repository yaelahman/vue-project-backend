<table>
    <thead>
        <tr>
            <th>No</th>
            <th width="130px">Nama</th>
            <th width="130px">Personel ID</th>
            <th width="130px">Departemen</th>
            <th width="130px">Status Absensi</th>
            <th width="130px">Tanggal Masuk</th>
            <th width="130px">Jam Masuk</th>
            <th width="130px">Tanggal Pulang</th>
            <th width="130px">Jam Pulang</th>
            <th width="130px">Menit Toleransi</th>
            <th width="130px">Menit Terlambat</th>
            <th width="130px">Denda</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($absensi as $index => $row)
            @if ($row->WorkPersonel && $row->WorkPersonel->getWorkPattern)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->Personel->m_personel_names }}</td>
                    <td>{{ $row->Personel->m_personel_personID }}</td>
                    <td>{{ isset($row->Personel->Departemen) ? $row->Personel->Departemen->m_departemen_name : '-' }}
                    </td>
                    <td>{{ $row->t_absensi_status == 1 ? 'WFO' : 'WFH' }}</td>
                    @php
                        $start = explode(' ', $row->t_absensi_startClock);
                        $end = explode(' ', $row->t_absensi_endClock);
                    @endphp
                    <td>{{ date('d-m-Y', strtotime($start[0])) }}</td>
                    <td>{{ date('H:i:s', strtotime($start[1])) }}</td>
                    <td>{{ isset($end) && isset($end[0]) ? date('d-m-Y', strtotime($end[0])) : '-' }}</td>
                    <td>{{ isset($end) && isset($end[1]) ? date('H:i:s', strtotime($end[1])) : '-' }}</td>
                    <td>{{ $row->WorkPersonel->getWorkPattern->m_work_patern_tolerance }}</td>
                    @php
                        $tolerance = strtotime($row->WorkPersonel->getWorkPattern->m_work_patern_tolerance);
                        $start = strtotime($start[1]);
                        $end = strtotime($row->WorkPersonel->getWorkSchedule->m_work_schedule_clockIn);
                        $result = $start - $end;
                        $menit = $result / 60 - $tolerance;
                    @endphp
                    <td>
                        {{ $menit < 0 ? 0 : explode('.', $menit)[0] }}
                    </td>
                    <td>
                        {{ number_format(($denda->m_device_settings_denda * ($menit < 0 ? 0 : explode('.', $menit)[0])), 0) }}
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
