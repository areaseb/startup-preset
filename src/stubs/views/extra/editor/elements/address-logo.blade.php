<table class="main" width="100%" cellspacing="0" cellpadding="0" border="0" data-types="background,padding,image-settings" align="center" data-last-type="background">
    <tbody>
        <tr>
            <td align="left" class="page-header element-content" style="padding-left:50px;padding-right:50px;padding-top:10px;padding-bottom:10px;background-color:#FFFFFF;text-align:center">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="text-align:left" contenteditable="true">
                            <img border="0" class="content-image" src="https://via.placeholder.com/150x50.jpg?text=150x50&w=150&h=50" style="display: inline-block;margin:0px;width:150px;height:50px">
                        </td>
                        <td contenteditable="true" style="text-align:right">
                            @php
                                $default = \App\Classes\Setting::where('model', 'Fatturazione')->first()->fields;
                            @endphp
                            {{$default['rag_soc']}}, <br> {{$default['indirizzo']}}<br>
                            <a %%%unsubscribe%%%>unsubscribe</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
