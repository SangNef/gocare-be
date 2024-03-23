@forelse($series as $key => $seri)
    <tr>
        <td>
            <input type="checkbox" class="ck_item" value="{{ $seri->id }}">
            {{ $seri->seri_number }}
        </td>
        <td>
            {{ $seri->activation_code }}
        </td>
        <td>
            {{ $seri->groupAttribute ? $seri->groupAttribute->attribute_value_texts : '' }}
        </td>
        <td>
            {{ \App\Models\ProductSeri::getQrCodeStatus()[$seri->qr_code_status]  }}
        </td>
        <td>
            {{ \App\Models\ProductSeri::getAvailableStockStatus()[$seri->stock_status]  }}
        </td>
        <td>
            {{ \App\Models\ProductSeri::getImportStatus()[$seri->status]  }}
        </td>
        <td>{{ $seri->created_at->format('d-m-Y') }}</td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center">No data</td></tr>
@endforelse
<tr>
    <td colspan="3">
        {{ $series->links() }}
    </td>
</tr>