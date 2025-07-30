


<div class="info-box">
  <span class="info-box-icon bg-info">
    <img src="{{ asset('img/svg/imis-icons/septic-tank.svg') }}" alt="Residential Icon">
  </span>
  <div class="info-box-content">
    <span class="info-box-text">
      <h3>{{ number_format($nonfunctionalSepticTank) }}</h3>
    </span>
    <span class="info-box-number">{{__('Non-Functional Septic Tank')}}</span>
  </div>
  <!-- Top-right icon with tooltip -->
  <span class="top-right-icon" data-tooltip="Culture & Religious<br>Agricultural & Farm">
    <i class="fa-solid fa-circle-info"></i>
    <div class="custom-tooltip">{{ __('Pit/ Holding Tank') }}<br>{{ __('Septic Tank connected to Drain Network') }} <br> {{ __('Septic Tank connected to Water Body') }}
    <br>{{ __('Septic Tank connected to Open Ground') }} <br> {{ __('Septic Tank without Outlet Connection') }}
    <br>{{ __('Septic Tank with Unknown Outlet Connection') }} <br> {{ __('Lined Pit connected to a Soak Pit') }}
    <br>{{ __('Lined Pit connected to Water Body') }} <br> {{ __('Lined Pit connected to Open Ground') }}
    <br>{{ __('Lined Pit connected to Drain Network') }} <br> {{ __('Lined Pit without Outlet') }}
    <br>{{ __('Lined Pit with Unknown Outlet Connection') }} <br> {{ __('Lined Pit with Impermeable Walls and Open Bottom') }}
    <br>{{ __('Double Pit') }} <br> {{ __('Permeable/ Unlined Pi') }}</div>
  </span>
</div>
