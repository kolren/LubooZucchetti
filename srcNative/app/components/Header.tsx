export function Header() {
  return (
    <div className="relative h-20 w-full">
      {/* Background with glass effect */}
      <div className="absolute inset-0 bg-[rgba(0,51,128,0.24)] rounded-[29px]" />
      
      {/* Logo */}
      <div className="absolute left-8 top-1/2 -translate-y-1/2">
        <div className="w-12 h-14 bg-gradient-to-br from-[#5588CA] via-[#5873A8] to-[#B3B5D7] rounded-2xl" />
      </div>
      
      {/* Navigation */}
      <div className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex gap-6">
        <button className="px-8 py-3 bg-[rgba(124,136,246,0.31)] rounded-[21px] text-white font-bold text-xl hover:bg-[rgba(124,136,246,0.45)] transition-colors">
          Dipendenti
        </button>
        <button className="px-8 py-3 bg-[rgba(159,167,241,0.65)] rounded-[21px] text-white font-bold text-xl hover:bg-[rgba(159,167,241,0.80)] transition-colors">
          Prenota
        </button>
        <button className="px-8 py-3 bg-[rgba(124,136,246,0.31)] rounded-[21px] text-white font-bold text-xl hover:bg-[rgba(124,136,246,0.45)] transition-colors">
          DashBoard
        </button>
        <button className="px-8 py-3 bg-[rgba(124,136,246,0.31)] rounded-[21px] text-white font-bold text-xl hover:bg-[rgba(124,136,246,0.45)] transition-colors">
          Gestisci
        </button>
      </div>
      
      {/* User Info */}
      <div className="absolute right-12 top-1/2 -translate-y-1/2">
        <div className="bg-gradient-to-r from-[#006360] to-[#00C9C3] opacity-45 px-6 py-4 rounded-2xl">
          <div className="text-[rgba(255,255,255,0.82)] text-sm font-bold">Amministratore</div>
          <div className="text-white text-lg font-bold">Ciao Valentina!</div>
        </div>
      </div>
      
      {/* Top right menu */}
      <div className="absolute right-8 top-2 flex gap-4 text-xs font-bold opacity-80">
        <button className="text-white hover:opacity-100">Modifica</button>
        <button className="text-white hover:opacity-100">Cambia utente</button>
        <button className="text-[#ffaeae] hover:opacity-100">Esci</button>
      </div>
    </div>
  );
}
