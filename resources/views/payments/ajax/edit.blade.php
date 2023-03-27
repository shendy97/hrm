<div class="row">
    <div class="col-sm-12">
        <x-form id="save-payment-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.payments.paymentDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="payment_project_id" :fieldLabel="__('app.project')"
                            fieldName="project_id" search="true">
                            <option value="0">--</option>
                            @foreach ($projects as $project)
                                <option @if ($project->id == $payment->project_id) selected @endif value="{{ $project->id }}" data-currency-id="{{ $project->currency_id }}"
                                    data-currency-code="{{ $project->currency->currency_code }}">
                                    {{ $project->project_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="payment_invoice_id" :fieldLabel="__('app.invoice')"
                            fieldName="invoice_id" search="true">
                            <option value="">--</option>
                            @if ($payment->invoice_id)
                                <option selected value="{{ $payment->invoice_id }}">
                                    {{ $payment->invoice->invoice_number }}</option>
                            @endif
                            @foreach ($invoices as $item)
                                @if ($payment->invoice_id != $item->id)
                                    <option value="{{ $item->id }}">{{ $item->invoice_number }}</option>
                                @endif
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="paid_on" :fieldLabel="__('modules.payments.paidOn')"
                            fieldName="paid_on" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="($payment->paid_on ? $payment->paid_on->format(company()->date_format) : '')" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.number fieldId="amount" :fieldLabel="__('modules.invoices.amount')" fieldName="amount"
                            :fieldValue="$payment->amount" :fieldPlaceholder="__('placeholders.price')"
                            fieldRequired="true" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <input type="hidden" id="payment_currency_id" name="payment_currency_id" value="{{ $payment->currency_id }}">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('app.currency')" fieldName="currency_id"
                            search="true">
                            @foreach ($currencies as $currency)
                                <option @if ($currency->id == $payment->currency_id) selected @endif value="{{ $currency->id }}" data-currency-code="{{ $currency->currency_code }}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')"
                        fieldName="exchange_rate" fieldRequired="true" :fieldValue="$payment->exchange_rate" :fieldReadOnly="($companyCurrency->id == $payment->currency_id)" 
                        fieldHelp="( {{company()->currency->currency_code}} {{__('app.to')}} {{$payment->currency->currency_code}} )" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="transaction_id" :fieldLabel="__('modules.payments.transactionId')"
                            fieldName="transaction_id" :fieldPlaceholder="__('placeholders.payments.transactionId')"
                            :fieldValue="$payment->transaction_id" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="payment_gateway_id" :fieldLabel="__('modules.payments.paymentGateway')" fieldName="gateway"
                        search="true">
                            <option value="">--</option>
                            <option @if ($payment->gateway == 'Offline') selected @endif value="Offline">{{ __('modules.offlinePayment.offlinePayment') }}</option>
                            @if ($paymentGateway->paypal_status == 'active')
                                <option @if ($payment->gateway == 'paypal') selected @endif value="paypal">{{ __('app.paypal') }}</option>
                            @endif
                            @if ($paymentGateway->stripe_status == 'active')
                                <option @if ($payment->gateway == 'stripe') selected @endif value="stripe">{{ __('app.stripe') }}</option>
                            @endif
                            @if ($paymentGateway->razorpay_status == 'active')
                                <option @if ($payment->gateway == 'stripe') selected @endif @if ($payment->gateway == 'paypal') selected @endif value="razorpay">{{ __('app.razorpay') }}</option>
                            @endif
                            @if ($paymentGateway->paystack_status == 'active')
                                <option @if ($payment->gateway == 'paystack') selected @endif value="paystack">{{ __('app.paystack') }}</option>
                            @endif
                            @if ($paymentGateway->mollie_status == 'active')
                                <option @if ($payment->gateway == 'mollie') selected @endif value="mollie">{{ __('app.mollie') }}</option>
                            @endif
                            @if ($paymentGateway->payfast_status == 'active')
                                <option @if ($payment->gateway == 'payfast') selected @endif value="payfast">{{ __('app.payfast') }}</option>
                            @endif
                            @if ($paymentGateway->authorize_status == 'active')
                                <option @if ($payment->gateway == 'authorize') selected @endif value="authorize">{{ __('app.authorize') }}</option>
                            @endif
                            @if ($paymentGateway->square_status == 'active')
                                <option @if ($payment->gateway == 'square') selected @endif value="square">{{ __('app.square') }}</option>
                            @endif
                            @if ($paymentGateway->flutterwave_status == 'active')
                                <option @if ($payment->gateway == 'flutterwave') selected @endif value="flutterwave">{{ __('app.flutterwave') }}</option>
                            @endif
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-dark-green f-10'></i> {{ __('app.complete') }}"
                                @if ($payment->status == 'complete') selected
                                @endif
                                value="complete">@lang('app.complete')</option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-yellow f-10'></i> {{ __('app.pending') }}"
                                @if ($payment->status == 'pending') selected
                                @endif
                                value="pending">@lang('app.pending')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.receipt')" fieldName="bill"
                            fieldId="bill" :fieldValue="($payment->bill ? $payment->file_url : '')" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.remark')"
                                fieldName="remarks" fieldId="remarks" :fieldValue="$payment->remarks"
                                :fieldPlaceholder="__('placeholders.payments.remark')" />
                        </div>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-payment-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('payments.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        datepicker('#paid_on', {
            position: 'bl',
            dateSelected: new Date("{{ $payment->paid_on ? str_replace('-', '/', $payment->paid_on) : str_replace('-', '/', now()) }}"),
            ...datepickerConfig
        });

        $('#save-payment-form').click(function() {
            const url = "{{ route('payments.update', $payment->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-payment-form",
                file: true,
                data: $('#save-payment-data-form').serialize()
            });
        });

        if ($('#payment_project_id').val() != '' && $('#payment_project_id').val() != 0){
            $('#currency_id').prop('disabled', true);
        } 

        $('#payment_project_id').change(function() {
            var companyCurrency = '{{ $companyCurrency->id }}';
            var companyCurrencyName = "{{$companyCurrency->currency_code}}";
            var currentCurrencyName = $('#payment_project_id option:selected').attr('data-currency-code');

            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            
            if ($('#payment_invoice_id').val() != '' || ($('#payment_project_id').val() != '' && $('#payment_project_id').val() != 0)) {
                var curId = $('#payment_project_id option:selected').attr('data-currency-id');
                $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )');

                $('#currency_id').removeAttr('disabled');
                $('#currency_id').selectpicker('refresh');
                $('#currency_id').val(curId);
                $('#currency_id').prop('disabled', true);
                $('#currency_id').selectpicker('refresh');
                $('#payment_currency_id').val(curId);
            } else {
                $('#currency_id').prop('disabled', false);
                $('#currency_id').selectpicker('refresh');
            }

            var id = $(this).val();
            var url = "{{ route('projects.invoice_list', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#save-payment-data-form',
                type: "POST",
                blockUI: true,
                data: {
                    'curId': curId,
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#payment_invoice_id').html(response.data);
                        $('#payment_invoice_id').selectpicker('refresh');
                        if(id != 0) {
                            $('#exchange_rate').val(response.exchangeRate);
                        }
                        if(curId != '' && curId != companyCurrency){
                            $('#exchange_rate').prop('readonly', false);
                        } else {
                            $('#exchange_rate').prop('readonly', true);
                        }
                    }
                }
            });
        });

        $('#currency_id').change(function() {
            var id = $(this).val();
            $('#payment_currency_id').val(id);
            var companyCurrencyName = "{{$companyCurrency->currency_code}}";
            var currentCurrencyName = $('#currency_id option:selected').attr('data-currency-code');
            var companyCurrency = '{{ $companyCurrency->id }}';

            if(id == companyCurrency){
                $('#exchange_rate').prop('readonly', true);
            } else{
                $('#exchange_rate').prop('readonly', false);
            }
            var url = "{{ route('invoices.get_exchange_rate', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                type: "GET",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#exchange_rate').val(response.data);
                        $('#exchange_rateHelp').html('( '+companyCurrencyName+' @lang('app.to') '+currentCurrencyName+' )');
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
