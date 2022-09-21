<table>
    <thead>
        <tr>
            <th>No</th>
            <th width="130px">Nama</th>
            <th width="130px">Personel ID</th>
            <th width="130px">Departemen</th>
            <th width="130px">Tanggal Pengajuan</th>
            <th width="130px">Tanggal Mulai Izin</th>
            <th width="130px">Jam Mulai Izin</th>
            <th width="130px">Jam Selesai Izin</th>
            <th width="130px">Jumlah Jam</th>
            <th width="130px">Keperluan</th>
            <th width="130px">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($permits as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->Personel->m_personel_names }}</td>
                <td>{{ $row->Personel->m_personel_personID }}</td>
                <td>{{ isset($row->Personel->Departemen) ? $row->Personel->Departemen->m_departemen_name : '-' }}</td>
                @php
                    $start = explode(' ', $row->permit_startclock);
                    $end = explode(' ', $row->permit_endclock);
                @endphp
                <td>{{ date('d-m-Y H:i:s', strtotime($row->created_at)) }}</td>
                <td>{{ date('d-m-Y', strtotime($start[0])) }}</td>
                <td>{{ date('H:i:s', strtotime($start[1])) }}</td>
                <td>{{ date('H:i:s', strtotime($end[1])) }}</td>
                @php
                    $start = strtotime($start[1]);
                    $end = strtotime($end[1]);
                    $result = $end - $start;
                    $jam = $result / 60 / 60;
                @endphp
                <td>{{ $jam < 0 ? 0 : explode('.', $jam)[0] }} Jam</td>
                {{-- <td>{{ $jam }}</td> --}}
                <td>{{ $row->permit_description }}</td>
                <td>{{ \App\Models\Permit::STATUS[$row->permit_status] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
