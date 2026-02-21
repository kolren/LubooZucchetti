import svgPaths from "./svg-fokf0ucj9p";

function Fill() {
  return <div className="bg-[rgba(0,51,128,0.24)] rounded-[29px] size-full" data-name="Fill" />;
}

function GlassEffect1() {
  return (
    <div className="bg-[rgba(0,0,0,0)] h-[50px] relative rounded-[21px] shrink-0 w-[141.059px]" data-name="Glass Effect">
      <div className="-translate-x-1/2 absolute flex h-[26px] items-center justify-center left-[70.56px] top-[12px] w-[119px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[26px] leading-[normal] not-italic relative text-[22px] text-center text-white w-[119px] whitespace-pre-wrap">Dipendenti</p>
        </div>
      </div>
    </div>
  );
}

function Container1() {
  return (
    <div className="bg-[rgba(124,136,246,0.31)] content-stretch flex flex-col h-[50px] items-center justify-center relative rounded-[21px] w-[141.059px]" data-name="Container">
      <GlassEffect1 />
    </div>
  );
}

function GlassEffect2() {
  return (
    <div className="bg-[rgba(0,0,0,0)] h-[50px] relative rounded-[21px] shrink-0 w-[141.059px]" data-name="Glass Effect">
      <div className="-translate-x-1/2 absolute flex h-[26px] items-center justify-center left-[70.56px] top-[12px] w-[97px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[26px] leading-[normal] not-italic relative text-[22px] text-center text-white w-[97px] whitespace-pre-wrap">Prenota</p>
        </div>
      </div>
    </div>
  );
}

function Container2() {
  return (
    <div className="bg-[rgba(159,167,241,0.65)] content-stretch flex flex-col h-[50px] items-center justify-center relative rounded-[21px] w-[141.059px]" data-name="Container">
      <GlassEffect2 />
    </div>
  );
}

function GlassEffect3() {
  return <div className="absolute bg-[rgba(0,0,0,0)] inset-[0_-0.12px_0_0.12px] rounded-[21px]" data-name="Glass Effect" />;
}

function Container3() {
  return (
    <div className="bg-[rgba(124,136,246,0.31)] content-stretch flex gap-[10px] h-[50px] items-center p-[14px] relative rounded-[21px] w-[141.059px]" data-name="Container">
      <GlassEffect3 />
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[22px] leading-[normal] not-italic relative text-[20px] text-center text-white w-[112px] whitespace-pre-wrap">DashBoard</p>
        </div>
      </div>
    </div>
  );
}

function GlassEffect4() {
  return <div className="absolute bg-[rgba(0,0,0,0)] inset-0 rounded-[21px]" data-name="Glass Effect" />;
}

function Container4() {
  return (
    <div className="bg-[rgba(124,136,246,0.31)] content-stretch flex gap-[10px] h-[50px] items-center p-[14px] relative rounded-[21px] w-[141.059px]" data-name="Container">
      <GlassEffect4 />
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[22px] leading-[normal] not-italic relative text-[20px] text-center text-white w-[112px] whitespace-pre-wrap">Gestisci</p>
        </div>
      </div>
    </div>
  );
}

function Frame2() {
  return (
    <div className="content-stretch flex gap-[22px] items-center pb-px relative">
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <Container1 />
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <Container2 />
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <Container3 />
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <Container4 />
        </div>
      </div>
    </div>
  );
}

function Group35() {
  return (
    <div className="absolute contents left-[983px] top-[19px]">
      <div className="absolute flex h-[16.532px] items-center justify-center left-[1007px] top-[19px] w-[114px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[16.532px] relative w-[114px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 114 16.5325">
              <path d={svgPaths.pe7d3880} fill="var(--fill-0, #008F8B)" id="Rectangle 12" />
            </svg>
          </div>
        </div>
      </div>
      <div className="-translate-x-1/2 absolute flex h-[15.662px] items-center justify-center left-[1064px] top-[19px] w-[108px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[15.662px] leading-[normal] not-italic relative text-[15px] text-[rgba(255,255,255,0.82)] text-center w-[108px] whitespace-pre-wrap">Amministratore</p>
        </div>
      </div>
      <div className="absolute flex h-[18.273px] items-center justify-center left-[983px] top-[38px] w-[138px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[18.273px] leading-[normal] not-italic relative text-[20px] text-white w-[138px] whitespace-pre-wrap">Ciao Valentina!</p>
        </div>
      </div>
    </div>
  );
}

function Group34() {
  return (
    <div className="absolute contents left-[964px] top-[7px]">
      <div className="absolute flex h-[62px] items-center justify-center left-[964px] top-[7px] w-[177px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[62px] relative w-[177px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 177 62">
              <path d={svgPaths.pc1d0500} fill="url(#paint0_linear_1_263)" fillOpacity="0.45" id="Rectangle 13" />
              <defs>
                <linearGradient gradientUnits="userSpaceOnUse" id="paint0_linear_1_263" x1="0" x2="177" y1="31" y2="31">
                  <stop stopColor="#006360" />
                  <stop offset="1" stopColor="#00C9C3" />
                </linearGradient>
              </defs>
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[62px] items-center justify-center left-[964px] top-[7px] w-[177px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[62px] relative w-[177px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 177 62">
              <path d={svgPaths.pc1d0500} fill="var(--fill-0, black)" fillOpacity="0.01" id="Rectangle 14" />
            </svg>
          </div>
        </div>
      </div>
      <Group35 />
    </div>
  );
}

function Frame11() {
  return (
    <div className="absolute content-stretch flex gap-[10px] items-center left-[48px] opacity-82 rounded-[45px] top-[33px]">
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[14px] leading-[normal] not-italic relative text-[#ffaeae] text-[12px] w-[23px] whitespace-pre-wrap">{`Esci                 `}</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[14px] leading-[normal] not-italic relative text-[12px] text-white w-[81px] whitespace-pre-wrap">Cambia utente</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[14px] leading-[normal] not-italic relative text-[12px] text-white w-[48px] whitespace-pre-wrap">Modifica</p>
        </div>
      </div>
    </div>
  );
}

function GlassEffect() {
  return (
    <div className="bg-[rgba(0,0,0,0)] relative rounded-[29px] size-full" data-name="Glass Effect">
      <div className="absolute flex items-center justify-center left-[292.76px] top-[11px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Frame2 />
        </div>
      </div>
      <Group34 />
      <Frame11 />
    </div>
  );
}

function Group13() {
  return (
    <div className="absolute contents inset-[24px_39px_643px_47px]">
      <div className="absolute flex inset-[24px_39px_643px_47px] items-center justify-center">
        <div className="-scale-y-100 flex-none h-[77px] rotate-180 w-[1237px]">
          <Fill />
        </div>
      </div>
      <div className="absolute flex inset-[24px_39px_643px_47px] items-center justify-center">
        <div className="-scale-y-100 flex-none h-[77px] rotate-180 w-[1237px]">
          <GlassEffect />
        </div>
      </div>
    </div>
  );
}

function Group12() {
  return (
    <div className="absolute h-[53px] left-[77px] top-[36px] w-[45px]" data-name="Group 1 3">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 45 53">
        <g clipPath="url(#clip0_1_236)" id="Group 1 3">
          <g filter="url(#filter0_di_1_236)" id="Group">
            <path d={svgPaths.p4abce00} fill="url(#paint0_linear_1_236)" id="Vector" />
            <path d={svgPaths.p31d2e700} fill="url(#paint1_linear_1_236)" fillOpacity="0.58" id="Vector_2" stroke="var(--stroke-0, black)" />
          </g>
          <path d={svgPaths.p4abce00} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector_3" />
          <path d={svgPaths.p81411a0} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector_4" />
        </g>
        <defs>
          <filter colorInterpolationFilters="sRGB" filterUnits="userSpaceOnUse" height="59.7871" id="filter0_di_1_236" width="53.7692" x="-5.18552" y="0">
            <feFlood floodOpacity="0" result="BackgroundImageFix" />
            <feColorMatrix in="SourceAlpha" result="hardAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" />
            <feOffset dy="4" />
            <feGaussianBlur stdDeviation="2" />
            <feComposite in2="hardAlpha" operator="out" />
            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
            <feBlend in2="BackgroundImageFix" mode="normal" result="effect1_dropShadow_1_236" />
            <feBlend in="SourceGraphic" in2="effect1_dropShadow_1_236" mode="normal" result="shape" />
            <feColorMatrix in="SourceAlpha" result="hardAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" />
            <feOffset dx="-6" dy="6" />
            <feGaussianBlur stdDeviation="10.45" />
            <feComposite in2="hardAlpha" k2="-1" k3="1" operator="arithmetic" />
            <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 0.995192 0 0 0 0 0.995192 0 0 0 0.29 0" />
            <feBlend in2="shape" mode="normal" result="effect2_innerShadow_1_236" />
          </filter>
          <linearGradient gradientUnits="userSpaceOnUse" id="paint0_linear_1_236" x1="-17.4745" x2="40.0444" y1="4.59119" y2="57.0625">
            <stop stopColor="#5588CA" />
            <stop offset="0.54144" stopColor="#5873A8" />
            <stop offset="0.735581" stopColor="#B3B5D7" />
          </linearGradient>
          <linearGradient gradientUnits="userSpaceOnUse" id="paint1_linear_1_236" x1="4.0724" x2="47.9904" y1="59.3479" y2="30.9231">
            <stop stopColor="#9172FF" />
            <stop offset="1" stopColor="#ADBAED" />
          </linearGradient>
          <clipPath id="clip0_1_236">
            <rect fill="white" height="53" width="45" />
          </clipPath>
        </defs>
      </svg>
    </div>
  );
}

function Container() {
  return (
    <div className="absolute contents left-[47px] top-[24px]" data-name="Container">
      <Group13 />
      <Group12 />
    </div>
  );
}

function Group3() {
  return (
    <div className="absolute contents left-[47px] top-[24px]">
      <Container />
    </div>
  );
}

function Fill1() {
  return <div className="absolute bg-[rgba(0,51,128,0.24)] inset-[3.06px_0_0_0] rounded-[34px]" data-name="Fill" />;
}

function GlassEffect6() {
  return <div className="absolute bg-[rgba(0,0,0,0)] h-[2px] left-[37px] rounded-[18px] top-[374.94px] w-[264px]" data-name="Glass Effect" />;
}

function GlassEffect5() {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] inset-[3.06px_0_0_0] rounded-[34px]" data-name="Glass Effect">
      <GlassEffect6 />
    </div>
  );
}

function Container5() {
  return (
    <div className="absolute contents inset-[3.06px_0_0_0]" data-name="Container">
      <Fill1 />
      <GlassEffect5 />
    </div>
  );
}

function Fill2() {
  return <div className="bg-[rgba(192,199,255,0.55)] col-1 h-[62.681px] ml-[0.46px] mt-[0.32px] rounded-[18px] row-1 w-[293.916px]" data-name="Fill" />;
}

function Frame4() {
  return (
    <div className="absolute content-stretch flex flex-col items-end justify-end left-[64.69px] top-[10.5px] w-[164px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[24px] leading-[normal] not-italic relative text-[20px] text-white w-full whitespace-pre-wrap">Postazione Base</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Thin',sans-serif] h-[16px] leading-[normal] not-italic relative text-[11px] text-white w-[205px] whitespace-pre-wrap">Scrivania + Cassettiera + Laptop + Armadietto</p>
        </div>
      </div>
    </div>
  );
}

function Fill3() {
  return (
    <div className="absolute contents left-[245.66px] top-[14px]" data-name="FILL">
      <div className="absolute flex h-[15.626px] items-center justify-center left-[252.35px] top-[33.82px] w-[23.305px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[15.626px] relative w-[23.305px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 23.3054 15.6262">
              <path d={svgPaths.p2a2cbcc0} fill="var(--fill-0, #C7DCFF)" id="Vector 7" />
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[20.537px] items-center justify-center left-[256.91px] top-[14px] w-[14.108px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[20.537px] relative w-[14.108px]">
            <div className="absolute inset-[0_0.27%_0_0.33%]">
              <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.0234 20.5373">
                <path d={svgPaths.p6cad900} fill="var(--fill-0, #C7DCFF)" id="Vector 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[26.431px] items-center justify-center left-[245.66px] top-[23.98px] w-[36.342px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[26.431px] relative w-[36.342px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.3421 26.4306">
              <path d={svgPaths.p9deaa00} fill="var(--fill-0, #9CC2FF)" id="Vector 5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}

function Glass() {
  return (
    <div className="absolute contents left-[245.66px] top-[14px]" data-name="GLASS">
      <div className="absolute flex h-[15.626px] items-center justify-center left-[252.35px] top-[33.82px] w-[23.305px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[15.626px] opacity-20 relative w-[23.305px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 23.3054 15.6262">
              <path d={svgPaths.p2a2cbcc0} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 7" />
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[20.537px] items-center justify-center left-[256.91px] top-[14px] w-[14.108px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[20.537px] opacity-20 relative w-[14.108px]">
            <div className="absolute inset-[0_0.27%_0_0.33%]">
              <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.0234 20.5373">
                <path d={svgPaths.p6cad900} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[26.431px] items-center justify-center left-[245.66px] top-[23.98px] w-[36.342px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[26.431px] opacity-20 relative w-[36.342px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.3421 26.4306">
              <path d={svgPaths.p9deaa00} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}

function Icon() {
  return (
    <div className="absolute contents left-[245.66px] top-[14px]" data-name="ICON">
      <Fill3 />
      <Glass />
    </div>
  );
}

function GlassEffect7() {
  return (
    <div className="bg-[rgba(0,0,0,0)] col-1 h-[64px] ml-0 mt-0 relative rounded-[18px] row-1 w-[294px]" data-name="Glass Effect">
      <Frame4 />
      <Icon />
    </div>
  );
}

function DeskMonitor() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid place-items-start relative shrink-0" data-name="Desk + monitor">
      <Fill2 />
      <GlassEffect7 />
    </div>
  );
}

function Fill4() {
  return (
    <div className="col-1 h-[64px] ml-0 mt-0 relative rounded-[18px] row-1 w-[294px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#262626] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-[rgba(255,255,255,0.1)] inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect8() {
  return <div className="bg-[rgba(0,0,0,0)] col-1 h-[64px] ml-px mt-0 rounded-[18px] row-1 w-[294px]" data-name="Glass Effect" />;
}

function Fill5() {
  return (
    <div className="h-[37.425px] relative w-[36.934px]" data-name="Fill">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.9345 37.425">
        <g id="Fill">
          <path d={svgPaths.p7bcb600} fill="var(--fill-0, #C7DCFF)" id="Vector 7" />
          <path d={svgPaths.p25a5fc00} fill="var(--fill-0, #C7DCFF)" id="Vector 6" />
          <path d={svgPaths.p20412600} fill="var(--fill-0, #9CC2FF)" id="Vector 5" />
          <path d={svgPaths.p2380b040} fill="var(--fill-0, white)" id="Rectangle 2" />
        </g>
      </svg>
    </div>
  );
}

function Glass1() {
  return (
    <div className="h-[37.425px] relative w-[36.934px]" data-name="GLASS">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.9345 37.425">
        <g id="GLASS" opacity="0.2">
          <path d={svgPaths.p7bcb600} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 7" />
          <path d={svgPaths.p25a5fc00} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 6" />
          <path d={svgPaths.p20412600} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 5" />
          <path d={svgPaths.p2380b040} fill="var(--fill-0, black)" fillOpacity="0.01" id="Rectangle 2" />
        </g>
      </svg>
    </div>
  );
}

function Icon1() {
  return (
    <div className="col-1 grid-cols-[max-content] grid-rows-[max-content] inline-grid ml-[210.07px] mt-[5px] place-items-start relative row-1" data-name="ICON">
      <div className="col-1 flex h-[37.425px] items-center justify-center ml-0 mt-0 relative row-1 w-[36.934px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Fill5 />
        </div>
      </div>
      <div className="col-1 flex h-[37.425px] items-center justify-center ml-0 mt-0 relative row-1 w-[36.934px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Glass1 />
        </div>
      </div>
    </div>
  );
}

function Frame1() {
  return (
    <div className="col-1 content-stretch flex flex-col items-start ml-0 mt-0 relative row-1 w-[196px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[24px] leading-[normal] not-italic relative text-[20px] text-white w-full whitespace-pre-wrap">Postazione Tech</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Thin',sans-serif] h-[16px] leading-[normal] not-italic relative text-[11px] text-white w-full whitespace-pre-wrap">{`Scrivania + Monitor  + Laptop + Cassettiera + Armadietto`}</p>
        </div>
      </div>
    </div>
  );
}

function Group2() {
  return (
    <div className="col-1 grid-cols-[max-content] grid-rows-[max-content] inline-grid ml-[33px] mt-[9px] place-items-start relative row-1">
      <Icon1 />
      <Frame1 />
    </div>
  );
}

function DeskMonitor1() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid place-items-start relative shrink-0" data-name="Desk + monitor">
      <Fill4 />
      <GlassEffect8 />
      <Group2 />
    </div>
  );
}

function Fill6() {
  return (
    <div className="col-1 h-[62px] ml-0 mt-0 relative rounded-[18px] row-1 w-[293.916px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#262626] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-[rgba(255,255,255,0.1)] inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect9() {
  return <div className="bg-[rgba(0,0,0,0)] col-1 h-[62px] ml-[0.08px] mt-0 rounded-[18px] row-1 w-[293.916px]" data-name="Glass Effect" />;
}

function Group4() {
  return (
    <div className="h-[42.74px] relative w-[43px]">
      <div className="absolute inset-[0_-1.16%_-1.17%_-1.16%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 44 43.2401">
          <g id="Group 5">
            <rect fill="var(--fill-0, #B4D4FF)" height="16.4079" id="Rectangle 3" rx="6.5" stroke="var(--stroke-0, #EAEFF4)" width="30.0483" x="6.97583" y="0.500007" />
            <g id="Vector">
              <path d={svgPaths.p2054b80} fill="var(--fill-0, #2D6FC7)" />
              <path d={svgPaths.p31435680} fill="var(--fill-0, #2D6FC7)" />
              <path d={svgPaths.pa85a700} fill="var(--fill-0, #2D6FC7)" />
              <path d={svgPaths.p248b8d00} stroke="var(--stroke-0, #EAEFF4)" strokeLinecap="round" />
            </g>
            <g id="Vector 14">
              <path d={svgPaths.p172eb500} fill="var(--fill-0, #86BAFF)" />
              <path d={svgPaths.p155cc700} stroke="var(--stroke-0, #7DAAE5)" />
            </g>
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group5() {
  return (
    <div className="h-[42.74px] relative w-[43px]">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 43 42.7401">
        <g id="Group 6" opacity="0.53">
          <rect fill="var(--fill-0, black)" fillOpacity="0.01" height="17.4079" id="Rectangle 3" rx="7" width="31.0483" x="5.97583" y="7.48038e-06" />
          <g id="Vector">
            <path d={svgPaths.p8391e00} fill="var(--fill-0, black)" fillOpacity="0.01" />
            <path d={svgPaths.p334e5d00} fill="var(--fill-0, black)" fillOpacity="0.01" />
            <path d={svgPaths.p1ba1d3f0} fill="var(--fill-0, black)" fillOpacity="0.01" />
          </g>
          <path d={svgPaths.p2bfd4740} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 14" />
        </g>
      </svg>
    </div>
  );
}

function Frame6() {
  return (
    <div className="col-1 content-stretch flex flex-col items-start ml-[32.75px] mt-[8.5px] relative row-1 w-[196px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[24px] leading-[normal] not-italic relative text-[20px] text-white w-full whitespace-pre-wrap">Sala Riunioni</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Thin',sans-serif] h-[16px] leading-[normal] not-italic relative text-[13px] text-white w-full whitespace-pre-wrap">Sala attrezzata</p>
        </div>
      </div>
    </div>
  );
}

function DeskOnly() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid place-items-start relative shrink-0" data-name="Desk Only">
      <Fill6 />
      <GlassEffect9 />
      <div className="col-1 flex h-[42.74px] items-center justify-center ml-[236.5px] mt-[10.5px] relative row-1 w-[43px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Group4 />
        </div>
      </div>
      <div className="col-1 flex h-[42.74px] items-center justify-center ml-[236.5px] mt-[10.5px] relative row-1 w-[43px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Group5 />
        </div>
      </div>
      <Frame6 />
    </div>
  );
}

function Fill7() {
  return (
    <div className="col-1 h-[62px] ml-0 mt-0 relative rounded-[18px] row-1 w-[293.916px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#3f8718] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-[rgba(255,255,255,0.1)] inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect10() {
  return <div className="bg-[rgba(0,0,0,0)] col-1 h-[62px] ml-[0.08px] mt-0 rounded-[18px] row-1 w-[293.916px]" data-name="Glass Effect" />;
}

function Fill8() {
  return (
    <div className="col-1 h-[30.1px] ml-0 mt-0 relative row-1 w-[44.072px]" data-name="Fill">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 44.0717 30.1">
        <g id="Fill">
          <path d={svgPaths.p1f227c00} fill="var(--fill-0, #77F1FF)" id="Vector 8" />
          <path d={svgPaths.pc6ac280} fill="var(--fill-0, #267580)" id="Vector 10" />
          <path d={svgPaths.p34d18500} fill="var(--fill-0, #267580)" id="Vector 11" />
        </g>
      </svg>
    </div>
  );
}

function GLass() {
  return (
    <div className="col-1 h-[30.006px] ml-0 mt-0 relative row-1 w-[44.072px]" data-name="GLass">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 44.0717 30.0062">
        <g id="GLass" opacity="0.59">
          <path d={svgPaths.paf73d00} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 9" />
        </g>
      </svg>
    </div>
  );
}

function Icon2() {
  return (
    <div className="col-1 grid-cols-[max-content] grid-rows-[max-content] inline-grid ml-[237.43px] mt-[16.5px] place-items-start relative row-1" data-name="ICON">
      <Fill8 />
      <GLass />
    </div>
  );
}

function Frame5() {
  return (
    <div className="col-1 content-stretch flex flex-col items-start ml-[32.5px] mt-[9.5px] relative row-1 w-[196px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[24px] leading-[normal] not-italic relative text-[20px] text-white w-full whitespace-pre-wrap">Posto Auto</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Thin',sans-serif] h-[16px] leading-[normal] not-italic relative text-[13px] text-white w-full whitespace-pre-wrap">Parcheggio</p>
        </div>
      </div>
    </div>
  );
}

function DeskOnly1() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid place-items-start relative shrink-0" data-name="Desk Only">
      <Fill7 />
      <GlassEffect10 />
      <Icon2 />
      <Frame5 />
    </div>
  );
}

function Frame() {
  return (
    <div className="content-stretch flex flex-col gap-[11px] h-[302px] items-center justify-center leading-[0] relative shrink-0 w-[297px]">
      <DeskMonitor />
      <DeskMonitor1 />
      <DeskOnly />
      <DeskOnly1 />
    </div>
  );
}

function Frame3() {
  return (
    <div className="absolute content-stretch flex flex-col items-center justify-center left-0 top-[26px] w-[339px]">
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="bg-clip-text font-['SF_Pro_Rounded:Bold',sans-serif] h-[43px] leading-[normal] not-italic relative text-[24px] text-center w-[339px] whitespace-pre-wrap" style={{ backgroundImage: "linear-gradient(103.15deg, rgb(255, 255, 255) 13.314%, rgb(173, 208, 255) 173%)", WebkitTextFillColor: "transparent" }}>
            Prenotazioni disponibili
          </p>
        </div>
      </div>
      <Frame />
    </div>
  );
}

function Fill9() {
  return (
    <div className="absolute inset-[0.82px_74px_1.15px_-20.08px] rounded-[18px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#262626] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-[#0dff00] inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect11() {
  return <div className="absolute bg-[rgba(0,0,0,0)] inset-[0.82px_74px_1.15px_-20.08px] rounded-[18px]" data-name="Glass Effect" />;
}

function Group() {
  return (
    <div className="absolute contents inset-[0.82px_74px_1.15px_-20.08px]">
      <Fill9 />
      <GlassEffect11 />
    </div>
  );
}

function LegendStuff() {
  return (
    <div className="absolute content-stretch flex gap-[14px] h-[17px] items-end left-0 top-0 w-[69px]" data-name="Legend stuff">
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Light',sans-serif] h-[17px] leading-[normal] not-italic relative text-[14px] text-white w-[65px] whitespace-pre-wrap">Disponibile</p>
        </div>
      </div>
      <Group />
    </div>
  );
}

function Fill10() {
  return (
    <div className="absolute inset-[0.82px_73.46px_1.15px_-19.71px] rounded-[18px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#262626] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-[#ff0004] inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect12() {
  return <div className="absolute bg-[rgba(0,0,0,0)] inset-[0.82px_73.46px_1.15px_-19.71px] rounded-[18px]" data-name="Glass Effect" />;
}

function Group1() {
  return (
    <div className="absolute contents inset-[0.82px_73.46px_1.15px_-19.71px]">
      <Fill10 />
      <GlassEffect12 />
    </div>
  );
}

function LegendStuff1() {
  return (
    <div className="absolute content-stretch flex gap-[14px] h-[17px] items-end left-0 top-[26px] w-[69px]" data-name="Legend stuff">
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Light',sans-serif] h-[17px] leading-[normal] not-italic relative text-[14px] text-white w-[65px] whitespace-pre-wrap">Occupato</p>
        </div>
      </div>
      <Group1 />
    </div>
  );
}

function Fill11() {
  return (
    <div className="col-1 h-[15.029px] ml-0 mt-0 relative rounded-[18px] row-1 w-[15.606px]" data-name="Fill">
      <div aria-hidden="true" className="absolute inset-0 pointer-events-none rounded-[18px]">
        <div className="absolute bg-[#262626] inset-0 mix-blend-color-dodge rounded-[18px]" />
        <div className="absolute bg-white inset-0 rounded-[18px]" />
      </div>
    </div>
  );
}

function GlassEffect13() {
  return <div className="bg-[rgba(0,0,0,0)] col-1 h-[15.029px] ml-0 mt-0 rounded-[18px] row-1 w-[15.606px]" data-name="Glass Effect" />;
}

function Group6() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid leading-[0] place-items-start relative shrink-0">
      <Fill11 />
      <GlassEffect13 />
    </div>
  );
}

function Frame9() {
  return (
    <div className="absolute content-stretch flex gap-[9px] items-center left-[-20.2px] top-[52px]">
      <Group6 />
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Light',sans-serif] h-[17px] leading-[normal] not-italic relative text-[14px] text-white w-[61px] whitespace-pre-wrap">Selezione</p>
        </div>
      </div>
    </div>
  );
}

function Frame8() {
  return (
    <div className="h-[69px] relative shrink-0 w-full">
      <LegendStuff />
      <LegendStuff1 />
      <Frame9 />
    </div>
  );
}

function Frame7() {
  return (
    <div className="absolute content-stretch flex flex-col gap-[13px] items-start left-[237px] top-[395px] w-[79px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="bg-clip-text font-['SF_Pro_Rounded:Bold',sans-serif] h-[20px] leading-[normal] not-italic relative text-[16px] w-full whitespace-pre-wrap" style={{ backgroundImage: "linear-gradient(96.6765deg, rgb(255, 255, 255) 13.314%, rgb(173, 208, 255) 173%)", WebkitTextFillColor: "transparent" }}>
            Legenda
          </p>
        </div>
      </div>
      <Frame8 />
    </div>
  );
}

function Bg() {
  return (
    <div className="h-[567px] relative w-[339px]" data-name="BG">
      <Container5 />
      <Frame3 />
      <Frame7 />
    </div>
  );
}

function PaginaPrincipale() {
  return (
    <div className="absolute contents left-0 top-0" data-name="Pagina Principale">
      <div className="absolute h-[744px] left-0 rounded-[45px] top-0 w-[1323px]" style={{ backgroundImage: "linear-gradient(-57.1906deg, rgb(48, 169, 255) 19.746%, rgb(0, 0, 0) 108.92%)" }} />
      <Group3 />
      <div className="absolute flex h-[567px] items-center justify-center left-[47px] top-[128px] w-[339px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Bg />
        </div>
      </div>
    </div>
  );
}

function Fill12() {
  return <div className="bg-[rgba(0,51,128,0.24)] rounded-[34px] size-full" data-name="Fill" />;
}

function Group16() {
  return (
    <div className="absolute contents left-[20px] top-[471px]">
      <div className="absolute flex h-[76.854px] items-center justify-center left-[20.15px] top-[471px] w-[405.85px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[76.854px] relative w-[405.85px]" data-name="Vector">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 405.85 76.8542">
              <g id="Vector">
                <path d={svgPaths.p1e8d8292} fill="var(--fill-0, white)" />
                <path d={svgPaths.p7ac7d00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p28749000} fill="var(--fill-0, white)" />
                <path d={svgPaths.p2ed92900} fill="var(--fill-0, white)" />
                <path d={svgPaths.p31d65c40} fill="var(--fill-0, white)" />
                <path d={svgPaths.p2498af00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p305f29f0} fill="var(--fill-0, white)" />
                <path d={svgPaths.p1b32300} fill="var(--fill-0, white)" />
                <path d={svgPaths.p19977d00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p21bc8100} fill="var(--fill-0, white)" />
                <path d={svgPaths.p1a595000} fill="var(--fill-0, white)" />
                <path d={svgPaths.p1cad400} fill="var(--fill-0, white)" />
                <path d={svgPaths.p1b3fbb80} fill="var(--fill-0, white)" />
                <path d={svgPaths.p35aae00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p2a75c480} fill="var(--fill-0, white)" />
                <path d={svgPaths.pcca0c00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p25bdc00} fill="var(--fill-0, white)" />
                <path d={svgPaths.p1b5e2080} fill="var(--fill-0, white)" />
              </g>
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[76.854px] items-center justify-center left-[20px] top-[471.41px] w-[405.85px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[76.854px] relative w-[405.85px]" data-name="Vector">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 405.85 76.8542">
              <g id="Vector" opacity="0.66">
                <path d={svgPaths.p1e8d8292} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p7ac7d00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p28749000} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p2ed92900} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p31d65c40} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p2498af00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p305f29f0} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p1b32300} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p19977d00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p21bc8100} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p1a595000} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p1cad400} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p1b3fbb80} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p35aae00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p2a75c480} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.pcca0c00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p25bdc00} fill="var(--fill-0, black)" fillOpacity="0.01" />
                <path d={svgPaths.p1b5e2080} fill="var(--fill-0, black)" fillOpacity="0.01" />
              </g>
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}

function Group18() {
  return (
    <div className="absolute contents left-[20px] top-[14px]">
      <div className="absolute flex h-[46px] items-center justify-center left-[20px] top-[14px] w-[142px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(198,101,213,0.6)] h-[46px] rounded-[19px] w-[142px]" />
        </div>
      </div>
      <div className="absolute flex h-[46px] items-center justify-center left-[20px] top-[14px] w-[142px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(0,0,0,0)] h-[46px] rounded-[19px] w-[142px]" />
        </div>
      </div>
      <div className="absolute flex h-[14px] items-center justify-center left-[37px] top-[32px] w-[18px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[14px] relative w-[18px]">
            <div className="absolute inset-[-7.14%_-5.56%_11.06%_-5.56%]">
              <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 20.0002 13.4516">
                <path d={svgPaths.p3643dc04} id="Vector 127" stroke="var(--stroke-0, white)" strokeLinecap="round" strokeWidth="2" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function GlassEffect14() {
  return (
    <div className="bg-[rgba(0,0,0,0)] relative rounded-[34px] size-full" data-name="Glass Effect">
      <Group16 />
      <div className="absolute flex h-[38px] items-center justify-center left-[551px] top-[26px] w-[248px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[38px] leading-[normal] not-italic relative text-[32px] text-white w-[248px] whitespace-pre-wrap">Postazione Base</p>
        </div>
      </div>
      <Group18 />
      <div className="absolute flex h-[28px] items-center justify-center left-[72px] top-[23px] w-[78px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[28px] leading-[normal] not-italic relative text-[24px] text-white w-[78px] whitespace-pre-wrap">Piano 1</p>
        </div>
      </div>
    </div>
  );
}

function Group20() {
  return (
    <div className="absolute contents left-0 top-0">
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(99,165,180,0.84)] h-[39px] rounded-[12px] w-[94px]" />
        </div>
      </div>
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(0,0,0,0)] h-[39px] rounded-[12px] w-[94px]" />
        </div>
      </div>
    </div>
  );
}

function Frame12() {
  return (
    <div className="content-stretch flex gap-[10px] h-[39px] items-center justify-end px-[15px] py-[6px] relative w-[94px]">
      <Group20 />
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Black',sans-serif] h-[27px] leading-[normal] not-italic relative text-[24px] text-white w-[64px] whitespace-pre-wrap">Aa1</p>
        </div>
      </div>
    </div>
  );
}

function Group33() {
  return (
    <div className="absolute contents left-[1046px] top-[240px]">
      <p className="absolute font-['SF_Pro_Rounded:Thin',sans-serif] h-[19px] leading-[normal] left-[1046px] not-italic text-[16px] text-white top-[240px] w-[94px] whitespace-pre-wrap">Armadietto</p>
      <div className="absolute flex h-[39px] items-center justify-center left-[1046px] top-[268px] w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Frame12 />
        </div>
      </div>
    </div>
  );
}

function Group21() {
  return (
    <div className="absolute contents left-0 top-0">
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[257px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(99,144,180,0.84)] h-[39px] rounded-[12px] w-[257px]" />
        </div>
      </div>
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[257px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(0,0,0,0)] h-[39px] rounded-[12px] w-[257px]" />
        </div>
      </div>
    </div>
  );
}

function Frame14() {
  return (
    <div className="h-[39px] relative w-[257px]">
      <Group21 />
      <div className="absolute flex h-[27px] items-center justify-center left-[89px] top-[6px] w-[149px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Light',sans-serif] h-[27px] leading-[normal] not-italic relative text-[24px] text-white w-[149px] whitespace-pre-wrap">17/01/2026</p>
        </div>
      </div>
    </div>
  );
}

function Group22() {
  return (
    <div className="absolute contents left-[904px] top-[414px]">
      <div className="absolute bg-[rgba(99,144,180,0.84)] h-[39px] left-[904px] rounded-[12px] top-[414px] w-[115px]" />
      <div className="absolute bg-[rgba(0,0,0,0)] h-[39px] left-[904px] rounded-[12px] top-[414px] w-[115px]" />
      <p className="absolute font-['SF_Pro_Rounded:Light',sans-serif] h-[27px] leading-[normal] left-[937px] not-italic text-[24px] text-white top-[420px] w-[50px] whitespace-pre-wrap">9:00</p>
    </div>
  );
}

function Group23() {
  return (
    <div className="absolute contents left-[1046px] top-[414px]">
      <div className="absolute bg-[rgba(99,144,180,0.84)] h-[39px] left-[1046px] rounded-[12px] top-[414px] w-[115px]" />
      <div className="absolute bg-[rgba(0,0,0,0)] h-[39px] left-[1046px] rounded-[12px] top-[414px] w-[115px]" />
      <p className="absolute font-['SF_Pro_Rounded:Light',sans-serif] h-[27px] leading-[normal] left-[1071px] not-italic text-[24px] text-white top-[420px] w-[65px] whitespace-pre-wrap">18:00</p>
    </div>
  );
}

function Group19() {
  return (
    <div className="absolute contents font-['SF_Pro_Rounded:Bold',sans-serif] leading-[normal] left-[904px] not-italic text-[24px] top-[206px] whitespace-pre-wrap">
      <p className="absolute h-[29px] left-[904px] text-white top-[206px] w-[283px]">Conferma prenotazione</p>
      <p className="absolute h-[29px] left-[904px] opacity-50 text-[rgba(0,0,0,0)] top-[206px] w-[283px]">Conferma prenotazione</p>
    </div>
  );
}

function Group25() {
  return (
    <div className="absolute contents left-0 top-0">
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(154,99,180,0.84)] h-[39px] rounded-[12px] w-[94px]" />
        </div>
      </div>
      <div className="absolute flex h-[39px] items-center justify-center left-0 top-0 w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="bg-[rgba(0,0,0,0)] h-[39px] rounded-[12px] w-[94px]" />
        </div>
      </div>
    </div>
  );
}

function Frame13() {
  return (
    <div className="content-stretch flex gap-[10px] h-[39px] items-center justify-end px-[15px] py-[6px] relative w-[94px]">
      <Group25 />
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Black',sans-serif] h-[27px] leading-[normal] not-italic relative text-[24px] text-white w-[30px] whitespace-pre-wrap">A1</p>
        </div>
      </div>
    </div>
  );
}

function Group32() {
  return (
    <div className="absolute contents left-[904px] top-[240px]">
      <p className="absolute font-['SF_Pro_Rounded:Thin',sans-serif] h-[19px] leading-[normal] left-[904px] not-italic text-[16px] text-white top-[240px] w-[94px] whitespace-pre-wrap">Posizione</p>
      <div className="absolute flex h-[39px] items-center justify-center left-[904px] top-[268px] w-[94px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Frame13 />
        </div>
      </div>
    </div>
  );
}

function Group26() {
  return (
    <div className="absolute contents left-[970px] top-[549px]">
      <div className="absolute bg-[rgba(180,123,99,0.84)] h-[39px] left-[970px] rounded-[12px] top-[549px] w-[115px]" />
      <div className="absolute bg-[rgba(0,0,0,0)] h-[39px] left-[970px] rounded-[12px] top-[549px] w-[115px]" />
      <p className="absolute font-['SF_Pro_Rounded:Semibold',sans-serif] h-[19px] leading-[normal] left-[992px] not-italic text-[16px] text-white top-[559px] w-[71px] whitespace-pre-wrap">Conferma</p>
    </div>
  );
}

function Frame10() {
  return (
    <div className="absolute content-stretch flex flex-col items-end justify-end left-[34px] top-[12px] w-[164px]">
      <div className="flex items-center justify-center relative shrink-0 w-full">
        <div className="-scale-y-100 flex-none rotate-180 w-full">
          <p className="font-['SF_Pro_Rounded:Bold',sans-serif] h-[24px] leading-[normal] not-italic relative text-[16px] text-white w-full whitespace-pre-wrap">Postazione Base</p>
        </div>
      </div>
      <div className="flex items-center justify-center relative shrink-0">
        <div className="-scale-y-100 flex-none rotate-180">
          <p className="font-['SF_Pro_Rounded:Thin',sans-serif] h-[16px] leading-[normal] not-italic relative text-[10px] text-white w-[205px] whitespace-pre-wrap">Scrivania + Cassettiera + Laptop + Armadietto</p>
        </div>
      </div>
    </div>
  );
}

function Fill13() {
  return (
    <div className="absolute contents left-[206.66px] top-[14px]" data-name="FILL">
      <div className="absolute flex h-[15.626px] items-center justify-center left-[213.35px] top-[33.82px] w-[23.305px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[15.626px] relative w-[23.305px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 23.3054 15.6262">
              <path d={svgPaths.p2a2cbcc0} fill="var(--fill-0, #C7DCFF)" id="Vector 7" />
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[20.537px] items-center justify-center left-[217.91px] top-[14px] w-[14.108px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[20.537px] relative w-[14.108px]">
            <div className="absolute inset-[0_0.27%_0_0.33%]">
              <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.0234 20.5373">
                <path d={svgPaths.p6cad900} fill="var(--fill-0, #C7DCFF)" id="Vector 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[26.431px] items-center justify-center left-[206.66px] top-[23.98px] w-[36.342px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[26.431px] relative w-[36.342px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.3421 26.4306">
              <path d={svgPaths.p9deaa00} fill="var(--fill-0, #9CC2FF)" id="Vector 5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}

function Glass2() {
  return (
    <div className="absolute contents left-[206.66px] top-[14px]" data-name="GLASS">
      <div className="absolute flex h-[15.626px] items-center justify-center left-[213.35px] top-[33.82px] w-[23.305px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[15.626px] opacity-20 relative w-[23.305px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 23.3054 15.6262">
              <path d={svgPaths.p2a2cbcc0} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 7" />
            </svg>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[20.537px] items-center justify-center left-[217.91px] top-[14px] w-[14.108px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[20.537px] opacity-20 relative w-[14.108px]">
            <div className="absolute inset-[0_0.27%_0_0.33%]">
              <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.0234 20.5373">
                <path d={svgPaths.p6cad900} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      <div className="absolute flex h-[26.431px] items-center justify-center left-[206.66px] top-[23.98px] w-[36.342px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <div className="h-[26.431px] opacity-20 relative w-[36.342px]">
            <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 36.3421 26.4306">
              <path d={svgPaths.p9deaa00} fill="var(--fill-0, black)" fillOpacity="0.01" id="Vector 5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  );
}

function Icon3() {
  return (
    <div className="absolute contents left-[206.66px] top-[14px]" data-name="ICON">
      <Fill13 />
      <Glass2 />
    </div>
  );
}

function GlassEffect15() {
  return (
    <div className="bg-[rgba(0,0,0,0)] relative rounded-[18px] size-full" data-name="Glass Effect">
      <Frame10 />
      <Icon3 />
    </div>
  );
}

function Group24() {
  return (
    <div className="absolute contents left-[904px] top-[206px]">
      <Group33 />
      <p className="absolute font-['SF_Pro_Rounded:Thin',sans-serif] h-[19px] leading-[normal] left-[904px] not-italic text-[16px] text-white top-[322px] w-[60px] whitespace-pre-wrap">Data</p>
      <div className="absolute flex h-[39px] items-center justify-center left-[904px] top-[346px] w-[257px]">
        <div className="-scale-y-100 flex-none rotate-180">
          <Frame14 />
        </div>
      </div>
      <Group22 />
      <Group23 />
      <p className="absolute font-['SF_Pro_Rounded:Thin',sans-serif] h-[19px] leading-[normal] left-[904px] not-italic text-[16px] text-white top-[390px] w-[60px] whitespace-pre-wrap">Inizio</p>
      <p className="absolute font-['SF_Pro_Rounded:Thin',sans-serif] h-[19px] leading-[normal] left-[1046px] not-italic text-[16px] text-white top-[394px] w-[60px] whitespace-pre-wrap">Fine</p>
      <Group19 />
      <Group32 />
      <Group26 />
      <div className="absolute flex inset-[472px_162px_208px_904px] items-center justify-center">
        <div className="-scale-y-100 flex-none h-[64px] rotate-180 w-[257px]">
          <GlassEffect15 />
        </div>
      </div>
    </div>
  );
}

function Container6() {
  return (
    <div className="absolute contents left-[436px] top-[131px]" data-name="Container">
      <div className="absolute flex inset-[131px_39px_49px_436px] items-center justify-center">
        <div className="-scale-y-100 flex-none h-[564px] rotate-180 w-[848px]">
          <Fill12 />
        </div>
      </div>
      <div className="absolute flex inset-[131px_39px_49px_436px] items-center justify-center">
        <div className="-scale-y-100 flex-none h-[564px] rotate-180 w-[848px]">
          <GlassEffect14 />
        </div>
      </div>
      <Group24 />
    </div>
  );
}

function Group14() {
  return (
    <div className="absolute h-[57.864px] left-[491.91px] top-[227.2px] w-[67.568px]">
      <div className="absolute inset-[-0.86%_-0.74%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 68.5682 58.8641">
          <g id="Group 13">
            <path d={svgPaths.p26012e10} id="Vector 8" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p218c4680} id="Vector 6" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1aa2c80} id="Vector 7" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p20dd4e40} id="Vector 9" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3243acc0} id="Vector 2" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3648b080} id="Vector 3" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p39b45b00} id="Vector 4" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pc42be00} id="Vector 5" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3e0af500} id="Vector 1" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p26ca2f00} id="Line 1" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p29151580} id="Line 2" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pebfb800} id="Vector 10" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group15() {
  return (
    <div className="absolute h-[57.864px] left-[696.56px] top-[227.2px] w-[67.568px]">
      <div className="absolute inset-[-0.86%_-0.74%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 68.5682 58.8641">
          <g id="Group 16">
            <path d={svgPaths.p20391380} id="Vector 38" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p157583c0} id="Vector 39" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p11781500} id="Vector 40" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.paeace80} id="Vector 41" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3243acc0} id="Vector 42" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p289afcc0} id="Vector 43" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p39b45b00} id="Vector 44" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1dcff540} id="Vector 45" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p25565600} id="Vector 46" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1b5b2d00} id="Line 7" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pf877940} id="Line 8" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pebfb800} id="Vector 47" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group17() {
  return (
    <div className="absolute h-[57.864px] left-[628.34px] top-[227.2px] w-[67.568px]">
      <div className="absolute inset-[-0.86%_-0.74%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 68.5682 58.8641">
          <g id="Group 15">
            <path d={svgPaths.p888a200} id="Vector 28" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p218c4680} id="Vector 29" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1aa2c80} id="Vector 30" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3e4aa980} id="Vector 31" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p321bf500} id="Vector 32" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p4c81180} id="Vector 33" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p265ed600} id="Vector 34" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p39532580} id="Vector 35" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p2f314b80} id="Vector 36" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p12861400} id="Line 5" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p29151580} id="Line 6" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pebfb800} id="Vector 37" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group27() {
  return (
    <div className="absolute h-[57.864px] left-[560.12px] top-[227.2px] w-[67.568px]">
      <div className="absolute inset-[-0.86%_-0.74%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 68.5682 58.8641">
          <g id="Group 14">
            <path d={svgPaths.p2548a98} id="Vector 18" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1328118} id="Vector 19" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3eca40f0} id="Vector 20" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3ff7b880} id="Vector 21" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p14dff860} id="Vector 22" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p30670400} id="Vector 23" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3e056fc0} id="Vector 24" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3207d440} id="Vector 25" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1699d780} id="Vector 26" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p31366500} id="Line 3" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p27e6c780} id="Line 4" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pebfb800} id="Vector 27" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group28() {
  return (
    <div className="absolute h-[57.864px] left-[765.2px] top-[227.2px] w-[67.568px]">
      <div className="absolute inset-[-0.86%_-0.74%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 68.5682 58.8641">
          <g id="Group 17">
            <path d={svgPaths.p2548a98} id="Vector 48" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p29463100} id="Vector 49" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3eca40f0} id="Vector 50" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p35901050} id="Vector 51" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pc090280} id="Vector 52" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p15ee8080} id="Vector 53" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p30cc7d80} id="Vector 54" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p155df848} id="Vector 55" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1f40880} id="Vector 56" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p33562f00} id="Line 9" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p27e6c780} id="Line 10" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pebfb800} id="Vector 57" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function MeetingRoom() {
  return (
    <div className="absolute contents left-[491.91px] top-[227.2px]" data-name="Meeting room">
      <Group14 />
      <Group15 />
      <Group17 />
      <Group27 />
      <Group28 />
    </div>
  );
}

function Group7() {
  return (
    <div className="absolute h-[46.592px] left-[772.11px] top-[537.31px] w-[60.444px]">
      <div className="absolute inset-[-1.07%_-0.83%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 61.4444 47.5917">
          <g id="Group 8">
            <path d={svgPaths.p301ca340} id="Vector 75" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p28c9ea00} id="Vector 76" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p19dbf800} fill="var(--fill-0, #36A482)" id="Ellipse 45" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p3a49f080} fill="var(--fill-0, #36A482)" id="Ellipse 46" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p1f771500} fill="var(--fill-0, #36A482)" id="Ellipse 47" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p459f600} fill="var(--fill-0, #36A482)" id="Ellipse 48" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p2bc8f460} id="Vector 77" stroke="var(--stroke-0, white)" />
            <path d="M6.54468 10.8974H2.65897" id="Vector 78" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p50f3b00} id="Vector 79" stroke="var(--stroke-0, white)" />
            <path d="M6.54468 25.012H2.88833" id="Vector 80" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group8() {
  return (
    <div className="absolute h-[46.592px] left-[710.37px] top-[537.31px] w-[60.444px]">
      <div className="absolute inset-[-1.07%_-0.83%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 61.4444 47.5917">
          <g id="Group 9">
            <path d={svgPaths.p321d9680} id="Vector 81" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p2f4db500} id="Vector 82" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p24610300} fill="var(--fill-0, #36A482)" id="Ellipse 49" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p1f6f6e00} fill="var(--fill-0, #36A482)" id="Ellipse 50" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p1c2db300} fill="var(--fill-0, #36A482)" id="Ellipse 51" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p17697480} fill="var(--fill-0, #36A482)" id="Ellipse 52" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.pc0b1000} id="Vector 83" stroke="var(--stroke-0, white)" />
            <path d="M6.54468 10.8974H2.65897" id="Vector 84" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p36973180} id="Vector 85" stroke="var(--stroke-0, white)" />
            <path d="M6.54468 25.012H2.88833" id="Vector 86" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group9() {
  return (
    <div className="absolute h-[46.592px] left-[648.63px] top-[537.31px] w-[60.444px]">
      <div className="absolute inset-[-1.07%_-0.83%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 61.4444 47.5916">
          <g id="Group 10">
            <path d={svgPaths.p14ff5f00} id="Vector 87" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3be94e00} id="Vector 88" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1fe3ebf0} fill="var(--fill-0, #36A482)" id="Ellipse 53" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p6d70000} fill="var(--fill-0, #36A482)" id="Ellipse 54" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p8a04c00} fill="var(--fill-0, #36A482)" id="Ellipse 55" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p36a38d50} fill="var(--fill-0, #36A482)" id="Ellipse 56" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p6263e00} id="Vector 89" stroke="var(--stroke-0, white)" />
            <path d="M6.54399 10.8974H2.65829" id="Vector 90" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pec39000} id="Vector 91" stroke="var(--stroke-0, white)" />
            <path d="M6.54399 25.012H2.88765" id="Vector 92" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group10() {
  return (
    <div className="absolute h-[46.592px] left-[586.89px] top-[537.31px] w-[60.444px]">
      <div className="absolute inset-[-1.07%_-0.83%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 61.4444 47.5917">
          <g id="Group 11">
            <path d={svgPaths.p321d9680} id="Vector 93" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p3000c380} id="Vector 94" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p249a4400} fill="var(--fill-0, #36A482)" id="Ellipse 57" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p31275df0} fill="var(--fill-0, #36A482)" id="Ellipse 58" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p180ec100} fill="var(--fill-0, #36A482)" id="Ellipse 59" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p1798e000} fill="var(--fill-0, #36A482)" id="Ellipse 60" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p24df9980} id="Vector 95" stroke="var(--stroke-0, white)" />
            <path d="M6.54445 10.8974H2.65874" id="Vector 96" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pbb47ac0} id="Vector 97" stroke="var(--stroke-0, white)" />
            <path d="M6.54445 25.012H2.8881" id="Vector 98" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group11() {
  return (
    <div className="absolute h-[46.592px] left-[524.72px] top-[537.31px] w-[60.444px]">
      <div className="absolute inset-[-1.07%_-0.83%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 61.4444 47.5917">
          <g id="Group 12">
            <path d={svgPaths.p2b571ff0} id="Vector 99" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p28c9ea00} id="Vector 100" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p2822e500} fill="var(--fill-0, #36A482)" id="Ellipse 61" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p24bfdd00} fill="var(--fill-0, #38FFC0)" id="Ellipse 63" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <g id="Ellipse 65" />
            <path d={svgPaths.p29fe0580} fill="var(--fill-0, #36A482)" id="Ellipse 64" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p2ccbfec0} id="Vector 101" stroke="var(--stroke-0, white)" />
            <path d="M6.54422 10.8975H2.65851" id="Vector 102" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p36973180} id="Vector 103" stroke="var(--stroke-0, white)" />
            <path d="M6.54422 25.012H2.88788" id="Vector 104" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function ScrivanieLaterali() {
  return (
    <div className="absolute contents left-[524.72px] top-[537.31px]" data-name="Scrivanie laterali">
      <Group7 />
      <Group8 />
      <Group9 />
      <Group10 />
      <Group11 />
    </div>
  );
}

function Group29() {
  return (
    <div className="absolute h-[199.519px] left-[784.75px] top-[311.92px] w-[49.646px]">
      <svg className="absolute block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 49.6458 199.519">
        <g id="Group 7">
          <path d={svgPaths.p104f4b00} fill="var(--fill-0, #1C6971)" id="Rectangle 11" />
          <path d={svgPaths.p2ac7d640} id="Vector 105" stroke="var(--stroke-0, white)" />
          <path d={svgPaths.p38919d00} id="Vector 106" stroke="var(--stroke-0, white)" />
          <path d={svgPaths.p17d79000} fill="var(--fill-0, #36A482)" id="Ellipse 65" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
          <path d={svgPaths.p16f29300} fill="var(--fill-0, #36A482)" id="Ellipse 66" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
          <path d={svgPaths.p1eb16a00} fill="var(--fill-0, #36A482)" id="Ellipse 67" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
          <path d={svgPaths.p30dc1d00} fill="var(--fill-0, #36A482)" id="Ellipse 68" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
          <path d={svgPaths.p39136380} id="Vector 107" stroke="var(--stroke-0, white)" />
          <path d="M13.0383 189.578V193.647" id="Vector 108" stroke="var(--stroke-0, white)" />
          <path d={svgPaths.p28d6b160} id="Vector 109" stroke="var(--stroke-0, white)" />
          <path d="M26.5197 189.685V193.513" id="Vector 110" stroke="var(--stroke-0, white)" />
        </g>
      </svg>
    </div>
  );
}

function Group30() {
  return (
    <div className="absolute h-[63.289px] left-[788.09px] top-[314.9px] w-[44.465px]">
      <div className="absolute inset-[-0.79%_-1.12%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 45.4647 64.2889">
          <g id="Group 5">
            <path d={svgPaths.p3df90300} id="Vector 117" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p38313100} id="Vector 118" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p16dc1200} fill="var(--fill-0, #36A482)" id="Ellipse 73" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p8a78900} fill="var(--fill-0, #36A482)" id="Ellipse 74" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p25f62200} fill="var(--fill-0, #36A482)" id="Ellipse 75" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.pfb57300} fill="var(--fill-0, #36A482)" id="Ellipse 76" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p2d6adc40} id="Vector 119" stroke="var(--stroke-0, white)" />
            <path d="M10.2007 57.3611V61.4297" id="Vector 120" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p71aa300} id="Vector 121" stroke="var(--stroke-0, white)" />
            <path d="M23.6819 57.4679V61.2964" id="Vector 122" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Group31() {
  return (
    <div className="absolute h-[63.289px] left-[788.08px] top-[379.54px] w-[44.466px]">
      <div className="absolute inset-[-0.79%_-1.12%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 45.4656 64.2889">
          <g id="Group 6">
            <path d={svgPaths.pc3ed40} id="Vector 111" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.pdcbaa70} id="Vector 112" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p1f21b380} fill="var(--fill-0, #36A482)" id="Ellipse 69" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p17592600} fill="var(--fill-0, #36A482)" id="Ellipse 70" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p1ea0e980} fill="var(--fill-0, #36A482)" id="Ellipse 71" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p11a43780} fill="var(--fill-0, #36A482)" id="Ellipse 72" stroke="var(--stroke-0, white)" strokeWidth="0.5" />
            <path d={svgPaths.p2f9c8700} id="Vector 113" stroke="var(--stroke-0, white)" />
            <path d="M10.2007 57.3611V61.4296" id="Vector 114" stroke="var(--stroke-0, white)" />
            <path d={svgPaths.p31b32f00} id="Vector 115" stroke="var(--stroke-0, white)" />
            <path d="M23.6824 57.4679V61.2963" id="Vector 116" stroke="var(--stroke-0, white)" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function ScrivanieLaterali1() {
  return (
    <div className="absolute contents left-[784.75px] top-[311.92px]" data-name="Scrivanie Laterali">
      <Group29 />
      <Group30 />
      <Group31 />
    </div>
  );
}

function ScrivanieConSChermo() {
  return (
    <div className="absolute h-[111.878px] left-[566.38px] top-[358.98px] w-[172.698px]" data-name="ScrivanieConSChermo">
      <div className="absolute inset-[-2.23%_-1.45%]">
        <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 177.698 116.878">
          <g id="ScrivanieConSChermo">
            <g id="Group 3">
              <path d={svgPaths.p166bc700} id="Ellipse 21" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p58b9000} id="Ellipse 22" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p9ef2740} id="Ellipse 23" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p20918260} id="Ellipse 24" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3fb4bb80} id="Ellipse 25" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p274bdd40} id="Ellipse 26" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p128e8d00} id="Ellipse 27" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p17be9280} id="Ellipse 28" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p186ddd00} id="Vector 59" stroke="var(--stroke-0, white)" />
            </g>
            <g id="Group 4">
              <path d={svgPaths.p2cd22300} id="Ellipse 29" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p20e5caf0} id="Ellipse 30" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p1916ee80} id="Ellipse 31" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3f105480} id="Ellipse 32" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3abac80} id="Ellipse 33" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p37b91f0} id="Ellipse 34" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p29d1a80} id="Ellipse 35" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3999d3f0} id="Ellipse 36" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p27d5b100} id="Vector 60" stroke="var(--stroke-0, white)" />
            </g>
            <g id="Group 2">
              <path d={svgPaths.p18ac2580} id="Ellipse 13" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p17475f00} id="Ellipse 14" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p2dcbd100} id="Ellipse 15" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.pc8035f0} id="Ellipse 16" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p21299e40} id="Ellipse 17" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p1b6703c0} id="Ellipse 18" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p41a10c0} id="Ellipse 19" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p8dc1e00} id="Ellipse 20" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3cb5fb40} id="Vector 58" stroke="var(--stroke-0, white)" />
            </g>
            <g id="Group 1">
              <path d={svgPaths.p7c0f700} id="Ellipse 12" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p2e54a900} id="Ellipse 11" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p2476d680} id="Ellipse 10" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p2c494100} id="Ellipse 9" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p3b4db400} id="Ellipse 8" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p1b0b6c70} id="Ellipse 7" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p1824ec80} id="Ellipse 6" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p19c70c80} id="Ellipse 5" stroke="var(--stroke-0, white)" />
              <path d={svgPaths.p2bef6a00} id="Vector 17" stroke="var(--stroke-0, white)" />
            </g>
            <path d={svgPaths.p24f4e580} id="Vector 126" stroke="var(--stroke-0, #FDFDFD)" strokeWidth="5" />
          </g>
        </svg>
      </div>
    </div>
  );
}

function Mappa() {
  return (
    <div className="absolute contents left-[485px] top-[222px]" data-name="MAPPA">
      <MeetingRoom />
      <div className="absolute bg-[#1c6971] h-[50px] left-[522px] rounded-[9px] top-[535px] w-[313px]" />
      <ScrivanieLaterali />
      <ScrivanieLaterali1 />
      <ScrivanieConSChermo />
      <div className="absolute h-[370.014px] left-[485px] top-[222px] w-[356.622px]" data-name="Vector">
        <div className="absolute inset-[-1.08%_-1.12%]">
          <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 364.622 378.014">
            <path d={svgPaths.pcd8de00} id="Vector" stroke="var(--stroke-0, #FDFDFD)" strokeWidth="8" />
          </svg>
        </div>
      </div>
    </div>
  );
}

export default function PaginaPrenotazioni() {
  return (
    <div className="relative size-full" data-name="Pagina Prenotazioni">
      <PaginaPrincipale />
      <Container6 />
      <Mappa />
    </div>
  );
}