<table>
    <thead>
        <tr>
            <th>No</th>
            <th width="130px">Nama</th>
            <th width="130px">Personel ID</th>
            <th width="130px">Departemen</th>
            <th width="130px">Tanggal Masuk</th>
            <th width="130px">Jam Masuk</th>
            <th width="130px">Tanggal Pulang</th>
            <th width="130px">Jam Pulang</th>
            <th width="350px">Catatan Kunjungan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($absensi as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->Personel->m_personel_names }}</td>
                <td>{{ $row->Personel->m_personel_personID }}</td>
                <td>{{ isset($row->Personel->Departemen) ? $row->Personel->Departemen->m_departemen_name : '-' }}</td>
                @php
                    $start = explode(' ', $row->t_absensi_startClock);
                    $end = explode(' ', $row->t_absensi_endClock);
                @endphp
                <td>{{ date('d-m-Y', strtotime($start[0])) }}</td>
                <td>{{ date('H:i:s', strtotime($start[1])) }}</td>
                <td>{{ isset($end) && isset($end[0]) ? date('d-m-Y', strtotime($end[0])) : '-' }}</td>
                <td>{{ isset($end) && isset($end[1]) ? date('H:i:s', strtotime($end[1])) : '-' }}</td>
                <td>{{ $row->t_absensi_catatan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
