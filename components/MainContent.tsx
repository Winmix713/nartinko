'use client'

import { useState } from 'react'
import MatchSelection from './MatchSelection'
import PredictionResults from './PredictionResults'
import Statistics from './Statistics'

interface MainContentProps {
  timeLeft: number
}

export default function MainContent({ timeLeft }: MainContentProps) {
  const [activeTab, setActiveTab] = useState('matchSelection')
  const [predictions, setPredictions] = useState([])

  const handlePredictionsGenerated = (newPredictions: any[]) => {
    setPredictions(newPredictions)
    setActiveTab('predictionResults')
  }

  return (
    <main className="container mx-auto px-4 py-8 flex-grow">
      <nav className="mb-8">
        <ul className="flex space-x-4">
          <li>
            <button
              onClick={() => setActiveTab('matchSelection')}
              className={`px-4 py-2 rounded-md ${activeTab === 'matchSelection' ? 'bg-[#CCFF00] text-black' : 'bg-gray-700 text-white'}`}
            >
              Mérkőzések kiválasztása
            </button>
          </li>
          <li>
            <button
              onClick={() => setActiveTab('predictionResults')}
              className={`px-4 py-2 rounded-md ${activeTab === 'predictionResults' ? 'bg-[#CCFF00] text-black' : 'bg-gray-700 text-white'}`}
            >
              Predikciók eredménye
            </button>
          </li>
          <li>
            <button
              onClick={() => setActiveTab('statistics')}
              className={`px-4 py-2 rounded-md ${activeTab === 'statistics' ? 'bg-[#CCFF00] text-black' : 'bg-gray-700 text-white'}`}
            >
              Statisztikák
            </button>
          </li>
        </ul>
      </nav>
      {activeTab === 'matchSelection' && <MatchSelection onPredictionsGenerated={handlePredictionsGenerated} timeLeft={timeLeft} />}
      {activeTab === 'predictionResults' && <PredictionResults predictions={predictions} />}
      {activeTab === 'statistics' && <Statistics predictions={predictions} />}
    </main>
  )
}

