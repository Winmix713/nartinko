'use client'

import { useState, useEffect } from 'react'
import { useUser } from '@/contexts/UserContext'
import Header from '@/components/Header'
import Footer from '@/components/Footer'
import PredictionResults from '@/components/PredictionResults'

export default function RecentPage() {
  const { user } = useUser()
  const [recentPredictions, setRecentPredictions] = useState([])

  useEffect(() => {
    const storedRecent = localStorage.getItem('recentPredictions')
    if (storedRecent) {
      setRecentPredictions(JSON.parse(storedRecent))
    }
  }, [])

  return (
    <>
      <Header />
      <main className="container mx-auto px-4 py-8 flex-grow">
        <h1 className="text-3xl font-bold mb-6 text-[#CCFF00]">Legutóbbi Predikciók</h1>
        <PredictionResults predictions={recentPredictions} />
      </main>
      <Footer />
    </>
  )
}

