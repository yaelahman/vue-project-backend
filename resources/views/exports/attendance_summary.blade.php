<table>
    <thead>
        <tr>
            <th>No</th>
            <th width="130px">Nama</th>
            <th width="130px">Departemen</th>
            <th width="130px">Kehadiran Hari</th>
            <th width="130px">Kehadiran Jam</th>
            <th width="130px">Terlambat</th>
            <th width="130px">Tidak Terlambat</th>
            <th width="130px">WFH</th>
            <th width="130px">Tidak Absen</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($attendance as $index => $row)
            @if ($row->WorkPersonel && $row->WorkPersonel->getWorkPattern)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->m_personel_names }}</td>
                    <td>{{ isset($row->Departemen) ? $row->Departemen->m_departemen_name : '-' }}</td>
                    <td>{{ $row->kehadiran }}</td>
                    <td>{{ $row->total_jam }}</td>
                    <td>{{ $row->terlambat }}</td>
                    <td>{{ $row->tidak_terlambat }}</td>
                    <td>{{ $row->wfh }}</td>
                    <td>{{ $row->tidak_hadir }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
