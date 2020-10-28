var item = [];
var items = [];
var itemsFromDB = ($('textarea#itemsToForm').val() != '') ? JSON.parse($('textarea#itemsToForm').val()) : [];

class Item
{
    constructor(id, nome, codice, descrizione, prezzo, perc_iva, qta, sconto, perc_sconto, esenzione_id)
    {
        this.uid = Math.random().toString(36).substr(2, 5);
        this.id = id;
        this.nome = nome;
        this.codice = codice;
        this.descrizione = descrizione;
        this.prezzo = parseFloat(prezzo);
        this.perc_iva = parseInt(perc_iva);
        this.sconto = sconto != 1 ? parseFloat(prezzo)*sconto : null;
        this.perc_sconto = sconto != 1 ? parseFloat(perc_sconto) : null;
        this.qta = parseFloat(qta).toFixed(2);
        this.exemption_id = (esenzione_id==null) ? null : esenzione_id;
        this.ivato = (sconto != 1) ? (parseFloat(prezzo)*sconto) * parseFloat(qta) * (parseInt(perc_iva)/100) : parseFloat(prezzo) * parseFloat(qta) * (parseInt(perc_iva)/100);
    }
    subtotal()
    {
        if(this.sconto == null)
        {
            return  (parseFloat(this.prezzo) * parseFloat(this.qta)) + parseFloat(this.ivato);
        }
        return ( parseFloat(this.sconto) * parseFloat(this.qta) ) + parseFloat(this.ivato);
    }
}

const getSconti = () => ({
    uno: $('input[name=sconto1]').val() ? $('input[name=sconto1]').val() : 0,
    due: $('input[name=sconto2]').val() ? $('input[name=sconto2]').val() : 0,
    tre: $('input[name=sconto3]').val() ? $('input[name=sconto3]').val() : 0
});

const getPercSconto = (s) => ((1-s)*100).toFixed(2);

const findSconto = (s) => (1-(parseInt(s.uno)/100))*(1-(parseInt(s.due)/100))*(1-(parseInt(s.tre)/100));

const resetItemForm = () => {
    $('#products').select2().val(null).trigger('change');
    $('textarea.desc').val('');
    $('input#prezzo').val('');
    $('input#perc_iva').val('');
    $('input.codice').val('');
    $('input[name="qta"]').val('1.00');
    $('input[name=sconto1]').val('');
    $('input[name=sconto2]').val('');
    $('input[name=sconto3]').val('');
    $('select[name="exemption_id"]').select2().val(null).trigger('change');
    let btn = $('button#addItem');
    btn.prop('disabled', true);
    btn.html('<i class="fa fa-plus"></i> AGGIUNGI VOCE');
    $('button#save').prop('disabled', false);
    if(btn.hasClass('edit'))
    {
        btn.removeClass('edit');
    }
}


const addItemToTable = (item) => {
    html = '<tr class="prodRowId-'+item.uid+'">';
        html += '<td class="pl-2">'+item.codice+'</td>';
        html += '<td>'+item.qta+'</td>';
        if(item.sconto != null)
        {
            html += '<td>'+(item.sconto.toFixed(2))+'</td>';
        }
        else
        {
            html += '<td>'+item.prezzo.toFixed(2)+'</td>';
        }
        if(item.perc_sconto != null)
        {
            html += '<td>'+(item.perc_sconto.toFixed(2))+'</td>';
        }
        else
        {
            html +='<td></td>';
        }
        html += '<td>'+item.ivato.toFixed(2)+'</td>';
        html += '<td>'+item.subtotal().toFixed(2)+'</td>';
        html += '<td class="pr-2">';
        html += '<a href="#" class="btn btn-sm removeProdRow" id="prodId-'+item.uid+'"><span class="text-danger"><i class="fa fa-trash"></i></span></a>';
        html += '<a href="#" class="btn btn-sm editProdRow" id="prodId-'+item.uid+'"><span class="text-warning"><i class="fa fa-edit"></i></span></a>';
        html += '</td>';
    html += '</tr>';
    $('.table.voci tbody').append(html);
    resetItemForm();
}


const addItemsToTable = (r) => {
    if(Object.entries(r).length !== 0)
    {
        Object.entries(r).forEach(([key, item]) => {
            let newItem = {};
            var sconto = item.sconto == 0 ? 1 : (1-item.sconto/100);
            var perc_sconto = item.sconto == 0 ? 0 : item.sconto;
            newItem = new Item(
                item.product_id,
                item.product.nome,
                item.product.codice,
                item.descrizione,
                item.importo,
                item.perc_iva,
                item.qta,
                sconto,
                perc_sconto,
                item.exception_id
            );

            items.push(newItem);
            addItemToTable(newItem);
        });
    }
}

addItemsToTable(itemsFromDB);

if(items.length)
{
    $('button#save').prop('disabled', false);
}


const isEmpty = (obj) => {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

const validate = () => {
    let tipo_doc = $('select[name="tipo_doc"] :selected').val();
    let countErrPa = 0;
    if(tipo_doc === 'Pu')
    {
        let checkPa = {};
        checkPa.pa_n_doc = $('input[name="pa_n_doc"]');
        checkPa.pa_cup = $('input[name="pa_cup"]');
        checkPa.pa_data_doc = $('input[name="pa_data_doc"]');
        checkPa.pa_cig = $('input[name="pa_cig"]');

        $.each(checkPa, function(){
            if($(this).val() == '')
            {
                $(this).addClass('is-invalid');
                countErrPa++;
            }
        })

        if(countErrPa != 0)
        {
            alertMe('Non hai compilato i campi per la Pubblica Amminstazione!');
        }
    }

    let countErrDDT = 0;
    let tipo = $('select[name="tipo"] :selected').val();
    if(tipo_doc === 'D')
    {
        let checkDDT = {};
        checkDDT.ddt_n_doc = $('input[name="ddt_n_doc"]');
        checkDDT.ddt_data_doc = $('input[name="ddt_data_doc"]');

        $.each(checkDDT, function(){
            if($(this).val() == '')
            {
                $(this).addClass('is-invalid');
                countErrDDT++;
            }
        });

        if(countErrDDT != 0)
        {
            alertMe('Non hai compilato i campi per il DDT!');
        }
    }

    if(isEmpty(items))
    {
        alertMe('Impossibile salvare la fattura: non hai caricato nessuna voce.');
        return false;
    }

    if((countErrPa+countErrDDT) === 0)
    {
        return true;
    }
    return false;
}

$('#invoiceForm').on('focusin', 'input.is-invalid', function(){
    $(this).removeClass('is-invalid');
});

const alertMe = (str) => {
    new Noty({
        text: str,
        type: 'error',
        theme: 'bootstrap-v4',
        timeout: 4000,
    }).show();
}
