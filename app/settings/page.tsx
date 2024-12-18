'use client'

import { useState, useEffect } from 'react'
import { useUser } from '@/contexts/UserContext'
import Header from '@/components/Header'
import Footer from '@/components/Footer'

export default function SettingsPage() {
  const { user } = useUser()
  const [settings, setSettings] = useState({
    darkMode: false,
    language: 'hu',
    notificationsEnabled: true
  })

  useEffect(() => {
    const storedSettings = localStorage.getItem('userSettings')
    if (storedSettings) {
      setSettings(JSON.parse(storedSettings))
    }
  }, [])

  const handleSettingChange = (setting: string, value: boolean | string) => {
    const newSettings = { ...settings, [setting]: value }
    setSettings(newSettings)
    localStorage.setItem('userSettings', JSON.stringify(newSettings))
  }

  return (
    <>
      <Header />
      <main className="container mx-auto px-4 py-8 flex-grow">
        <h1 className="text-3xl font-bold mb-6 text-[#CCFF00]">Beállítások</h1>
        <div className="bg-[#141414]/50 backdrop-blur-md border border-[#CCFF00]/20 rounded-2xl p-6 mb-8">
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <label htmlFor="darkModeToggle" className="text-lg">Sötét mód</label>
              <input
                type="checkbox"
                id="darkModeToggle"
                checked={settings.darkMode}
                onChange={(e) => handleSettingChange('darkMode', e.target.checked)}
                className="toggle"
              />
            </div>
            <div className="flex items-center justify-between">
              <label htmlFor="languageSelect" className="text-lg">Nyelv</label>
              <select
                id="languageSelect"
                value={settings.language}
                onChange={(e) => handleSettingChange('language', e.target.value)}
                className="bg-[#141414] border border-[#CCFF00]/20 rounded-md text-white p-2"
              >
                <option value="hu">Magyar</option>
                <option value="en">English</option>
              </select>
            </div>
            <div className="flex items-center justify-between">
              <label htmlFor="notificationsToggle" className="text-lg">Értesítések</label>
              <input
                type="checkbox"
                id="notificationsToggle"
                checked={settings.notificationsEnabled}
                onChange={(e) => handleSettingChange('notificationsEnabled', e.target.checked)}
                className="toggle"
              />
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </>
  )
}

