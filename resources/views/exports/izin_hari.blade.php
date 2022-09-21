<table>
    <thead>
        <tr>
            <th>No</th>
            <th width="130px">Nama</th>
            <th width="130px">Personel ID</th>
            <th width="130px">Departemen</th>
            <th width="130px">Tanggal Pengajuan</th>
            <th width="130px">Tanggal Izin</th>
            <th width="130px">Lama Izin</th>
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
                <td>
                    @foreach ($row->PermitDate as $item)
                        {{ date('d-m-Y', strtotime($item->permit_date)) }};
                    @endforeach
                </td>
                <td>{{ count($row->PermitDate) }} Hari</td>
                {{-- <td>{{ $jam }}</td> --}}
                <td>{{ $row->permit_description }}</td>
                <td>{{ \App\Models\Permit::STATUS[$row->permit_status] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
