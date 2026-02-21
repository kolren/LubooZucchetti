import { useState } from 'react';
import { Header } from './components/Header';
import { Sidebar } from './components/Sidebar';
import { SeatingGrid } from './components/SeatingGrid';
import { BookingForm } from './components/BookingForm';

interface Seat {
  id: string;
  status: 'available' | 'occupied' | 'selected';
  row: number;
  col: number;
}

interface Station {
  id: string;
  name: string;
  description: string;
  icon: string;
  isSelected: boolean;
}

export default function App() {
  const [stations, setStations] = useState<Station[]>([
    {
      id: 'base',
      name: 'Postazione Base',
      description: 'Scrivania + Cassettiera + Laptop + Armadietto',
      icon: '🖥️',
      isSelected: true,
    },
    {
      id: 'tech',
      name: 'Postazione Tech',
      description: 'Scrivania + Monitor + Laptop + Cassettiera + Armadietto',
      icon: '💻',
      isSelected: false,
    },
    {
      id: 'meeting',
      name: 'Sala Riunioni',
      description: 'Sala attrezzata',
      icon: '👥',
      isSelected: false,
    },
    {
      id: 'parking',
      name: 'Posto Auto',
      description: 'Parcheggio',
      icon: '🚗',
      isSelected: false,
    },
  ]);

  const [seats, setSeats] = useState<Seat[]>([
    // Top row
    { id: 'A1', status: 'available', row: 0, col: 0 },
    { id: 'A2', status: 'available', row: 0, col: 1 },
    { id: 'A3', status: 'occupied', row: 0, col: 2 },
    { id: 'A4', status: 'available', row: 0, col: 3 },
    { id: 'A5', status: 'available', row: 0, col: 4 },
    // Middle rows
    { id: 'B1', status: 'available', row: 1, col: 0 },
    { id: 'B2', status: 'available', row: 1, col: 1 },
    { id: 'C1', status: 'occupied', row: 2, col: 0 },
    { id: 'C2', status: 'available', row: 2, col: 1 },
    // Bottom row
    { id: 'D1', status: 'available', row: 3, col: 0 },
    { id: 'D2', status: 'available', row: 3, col: 1 },
    { id: 'D3', status: 'available', row: 3, col: 2 },
    { id: 'D4', status: 'occupied', row: 3, col: 3 },
    { id: 'D5', status: 'available', row: 3, col: 4 },
  ]);

  const [selectedSeat, setSelectedSeat] = useState<string | null>(null);

  const handleStationSelect = (stationId: string) => {
    setStations(stations.map(s => ({
      ...s,
      isSelected: s.id === stationId
    })));
    // Reset seat selection when changing station
    setSeats(seats.map(s => ({
      ...s,
      status: s.status === 'selected' ? 'available' : s.status
    })));
    setSelectedSeat(null);
  };

  const handleSeatClick = (seatId: string) => {
    const seat = seats.find(s => s.id === seatId);
    if (!seat || seat.status === 'occupied') return;

    setSeats(seats.map(s => {
      if (s.id === seatId) {
        return { ...s, status: s.status === 'selected' ? 'available' : 'selected' };
      }
      // Deselect other seats
      if (s.status === 'selected') {
        return { ...s, status: 'available' };
      }
      return s;
    }));

    setSelectedSeat(seat.status === 'selected' ? null : seatId);
  };

  const handleConfirm = () => {
    if (!selectedSeat) return;
    
    // Mark seat as occupied
    setSeats(seats.map(s => 
      s.id === selectedSeat ? { ...s, status: 'occupied' } : s
    ));
    
    setSelectedSeat(null);
    alert(`Prenotazione confermata per il posto ${selectedSeat}!`);
  };

  const selectedStation = stations.find(s => s.isSelected);

  return (
    <div 
      className="min-h-screen w-full p-12" 
      style={{ 
        background: 'linear-gradient(to top right, rgb(48, 169, 255) 0%, rgb(0, 0, 0) 100%)' 
      }}
    >
      <div className="max-w-[1400px] mx-auto space-y-8">
        {/* Header */}
        <Header />

        {/* Main Content */}
        <div className="flex gap-6">
          {/* Sidebar */}
          <Sidebar 
            stations={stations} 
            onStationSelect={handleStationSelect} 
          />

          {/* Seating Grid */}
          <div className="flex-1">
            <SeatingGrid 
              seats={seats} 
              onSeatClick={handleSeatClick}
              selectedStation={selectedStation?.name || 'Postazione Base'}
            />
          </div>

          {/* Booking Form */}
          <BookingForm 
            selectedSeat={selectedSeat}
            selectedStation={selectedStation?.name || 'Postazione Base'}
            stationDescription={selectedStation?.description || ''}
            onConfirm={handleConfirm}
          />
        </div>
      </div>
    </div>
  );
}
