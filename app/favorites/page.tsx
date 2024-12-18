'use client'

import { useState, useEffect } from 'react'
import { useUser } from '@/contexts/UserContext'
import Header from '@/components/Header'
import Footer from '@/components/Footer'
import PredictionResults from '@/components/PredictionResults'

export default function FavoritesPage() {
  const { user } = useUser()
  const [favoritePredictions, setFavoritePredictions] = useState([])

  useEffect(() => {
    const storedFavorites = localStorage.getItem('favoritePredictions')
    if (storedFavorites) {
      setFavoritePredictions(JSON.parse(storedFavorites))
    }
  }, [])

  return (
    <>
      <Header />
      <main className="container mx-auto px-4 py-8 flex-grow">
        <h1 className="text-3xl font-bold mb-6 text-[#CCFF00]">Kedvenc Predikciók</h1>
        <PredictionResults predictions={favoritePredictions} />
      </main>
      <Footer />
    </>
  )
}

