<table class="main" width="100%" cellspacing="0" cellpadding="0" border="0" data-types="background,padding" align="center" data-last-type="background">
    <tbody>
        <tr>
            <td align="left" class="page-header element-content" style="padding-left:50px;padding-right:50px;padding-top:10px;padding-bottom:10px;background-color:#FFFFFF;text-align:center">
                <div contenteditable="true">
                    @php
                        $default = \App\Classes\Setting::where('model', 'Fatturazione')->first()->fields;
                    @endphp
                    {{$default['rag_soc']}}, {{$default['indirizzo']}}<br>
                    <a %%%unsubscribe%%%>unsubscribe</a>
                </div>
            </td>
        </tr>
    </tbody>
</table>
