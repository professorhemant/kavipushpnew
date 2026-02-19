export class StorageService {
  private static fallbackData = new Map<string, string>();
  private static isLocalStorageAvailable = true;
  private static lastStorageTest = 0;
  private static testInterval = 5000; // Test every 5 seconds if localStorage becomes available

  static {
    // Test localStorage availability on initialization
    this.testLocalStorageAvailability();
  }

  private static testLocalStorageAvailability(): boolean {
    try {
      const testKey = 'storage_test_' + Date.now();
      const testValue = 'test_value';
      
      // Clear any existing test keys first
      try {
        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i);
          if (key && key.startsWith('storage_test_')) {
            localStorage.removeItem(key);
          }
        }
      } catch (e) {
        // Ignore cleanup errors
      }
      
      localStorage.setItem(testKey, testValue);
      const retrieved = localStorage.getItem(testKey);
      localStorage.removeItem(testKey);
      
      this.isLocalStorageAvailable = retrieved === testValue;
      this.lastStorageTest = Date.now();
      
      console.log('=== STORAGE AVAILABILITY TEST ===');
      console.log('localStorage availability test:', this.isLocalStorageAvailable ? 'PASSED' : 'FAILED');
      console.log('Browser localStorage support:', typeof(Storage) !== "undefined");
      console.log('Private browsing detected:', this.isPrivateBrowsing());
      console.log('Current localStorage items:', localStorage.length);
      
      if (this.isLocalStorageAvailable) {
        // If localStorage is now available, try to restore data from fallback
        this.restoreFromFallback();
      }
      
      return this.isLocalStorageAvailable;
    } catch (error) {
      console.error('localStorage test failed:', error);
      console.log('Error type:', error.constructor.name);
      console.log('Error message:', error.message);
      this.isLocalStorageAvailable = false;
      this.lastStorageTest = Date.now();
      return false;
    }
  }

  private static isPrivateBrowsing(): boolean {
    try {
      // Test for private browsing mode
      const testKey = 'private_browsing_test';
      localStorage.setItem(testKey, 'test');
      localStorage.removeItem(testKey);
      return false;
    } catch (e) {
      return true;
    }
  }

  private static restoreFromFallback(): void {
    if (this.fallbackData.size === 0) return;
    
    try {
      console.log('Attempting to restore data from fallback to localStorage...');
      let restoredCount = 0;
      
      for (const [key, value] of this.fallbackData.entries()) {
        try {
          localStorage.setItem(key, value);
          const verification = localStorage.getItem(key);
          if (verification === value) {
            restoredCount++;
          }
        } catch (error) {
          console.warn(`Failed to restore key ${key} to localStorage:`, error);
        }
      }
      
      if (restoredCount > 0) {
        console.log(`Successfully restored ${restoredCount} items from fallback to localStorage`);
      }
    } catch (error) {
      console.error('Error during fallback restoration:', error);
    }
  }

  private static shouldRetestStorage(): boolean {
    return !this.isLocalStorageAvailable && 
           (Date.now() - this.lastStorageTest) > this.testInterval;
  }

  static setItem(key: string, value: string): boolean {
    console.log(`=== STORAGE SET ATTEMPT: ${key} ===`);
    console.log('Value length:', value.length);
    console.log('Timestamp:', new Date().toISOString());
    
    // Periodically retest localStorage if it was previously unavailable
    if (this.shouldRetestStorage()) {
      console.log('Retesting storage availability...');
      this.testLocalStorageAvailability();
    }

    try {
      // Try localStorage first
      if (this.isLocalStorageAvailable) {
        console.log('Attempting localStorage save...');
        localStorage.setItem(key, value);
        
        // Verify it was saved
        const verification = localStorage.getItem(key);
        if (verification === value) {
          console.log(`✅ Successfully saved to localStorage: ${key} (${value.length} chars)`);
          console.log('Current localStorage size:', Object.keys(localStorage).length, 'items');
          // Also keep in fallback for redundancy
          this.fallbackData.set(key, value);
          return true;
        } else {
          console.warn(`❌ localStorage verification failed for key: ${key}`);
          console.log('Expected length:', value.length, 'Got length:', verification?.length || 0);
          this.isLocalStorageAvailable = false;
        }
      } else {
        console.log('localStorage not available, using fallback...');
      }
      
      // Fallback to in-memory storage
      this.fallbackData.set(key, value);
      console.log(`💾 Saved to fallback storage: ${key} (${value.length} chars)`);
      console.log('Fallback storage size:', this.fallbackData.size, 'items');
      return true;
    } catch (error) {
      console.error(`❌ Failed to save ${key}:`, error);
      console.log('Error type:', error.constructor.name);
      console.log('Error message:', error.message);
      this.isLocalStorageAvailable = false;
      
      // Try fallback storage
      try {
        this.fallbackData.set(key, value);
        console.log(`💾 Saved to fallback storage after localStorage error: ${key}`);
        return true;
      } catch (fallbackError) {
        console.error(`❌ Fallback storage also failed for ${key}:`, fallbackError);
        return false;
      }
    }
  }

  static getItem(key: string): string | null {
    console.log(`=== STORAGE GET ATTEMPT: ${key} ===`);
    console.log('Timestamp:', new Date().toISOString());
    
    // Periodically retest localStorage if it was previously unavailable
    if (this.shouldRetestStorage()) {
      console.log('Retesting storage availability...');
      this.testLocalStorageAvailability();
    }

    try {
      // Try localStorage first
      if (this.isLocalStorageAvailable) {
        console.log('Checking localStorage...');
        const value = localStorage.getItem(key);
        if (value !== null) {
          console.log(`✅ Retrieved from localStorage: ${key} (${value.length} chars)`);
          // Also cache in fallback for redundancy
          this.fallbackData.set(key, value);
          return value;
        } else {
          console.log(`⚠️ Key not found in localStorage: ${key}`);
        }
      } else {
        console.log('localStorage not available, checking fallback...');
      }
      
      // Fallback to in-memory storage
      const fallbackValue = this.fallbackData.get(key) || null;
      if (fallbackValue !== null) {
        console.log(`💾 Retrieved from fallback storage: ${key} (${fallbackValue.length} chars)`);
      } else {
        console.log(`❌ Key not found in any storage: ${key}`);
        console.log('Available fallback keys:', Array.from(this.fallbackData.keys()));
        console.log('Available localStorage keys:', this.isLocalStorageAvailable ? Object.keys(localStorage) : 'N/A');
      }
      return fallbackValue;
    } catch (error) {
      console.error(`❌ Failed to retrieve ${key}:`, error);
      console.log('Error type:', error.constructor.name);
      console.log('Error message:', error.message);
      this.isLocalStorageAvailable = false;
      
      // Try fallback storage
      const fallbackValue = this.fallbackData.get(key) || null;
      if (fallbackValue !== null) {
        console.log(`💾 Retrieved from fallback storage after localStorage error: ${key}`);
      }
      return fallbackValue;
    }
  }

  static removeItem(key: string): void {
    try {
      if (this.isLocalStorageAvailable) {
        localStorage.removeItem(key);
      }
    } catch (error) {
      console.error(`Failed to remove from localStorage: ${key}`, error);
      this.isLocalStorageAvailable = false;
    }
    
    this.fallbackData.delete(key);
    console.log(`Removed from storage: ${key}`);
  }

  static clear(): void {
    try {
      if (this.isLocalStorageAvailable) {
        localStorage.clear();
      }
    } catch (error) {
      console.error('Failed to clear localStorage:', error);
      this.isLocalStorageAvailable = false;
    }
    
    this.fallbackData.clear();
    console.log('Cleared all storage');
  }

  static getStorageInfo(): {
    type: 'localStorage' | 'fallback' | 'none';
    keys: string[];
    isAvailable: boolean;
    fallbackSize: number;
    localStorageSize: number;
    debugInfo: any;
  } {
    const fallbackKeys = Array.from(this.fallbackData.keys());
    let localStorageKeys: string[] = [];
    let localStorageSize = 0;
    
    if (this.isLocalStorageAvailable) {
      try {
        localStorageKeys = Object.keys(localStorage);
        localStorageSize = localStorageKeys.length;
      } catch (error) {
        console.error('Failed to get localStorage keys:', error);
      }
    }
    
    const debugInfo = {
      browserSupportsLocalStorage: typeof(Storage) !== "undefined",
      isPrivateBrowsing: this.isPrivateBrowsing(),
      userAgent: navigator.userAgent,
      storageQuotaExceeded: false,
      lastStorageTest: new Date(this.lastStorageTest).toISOString(),
    };

    return {
      type: this.isLocalStorageAvailable ? 'localStorage' : (fallbackKeys.length > 0 ? 'fallback' : 'none'),
      keys: this.isLocalStorageAvailable ? localStorageKeys : fallbackKeys,
      isAvailable: this.isLocalStorageAvailable || fallbackKeys.length > 0,
      fallbackSize: fallbackKeys.length,
      localStorageSize,
      debugInfo
    };
  }

  static retestAvailability(): boolean {
    console.log('Manual storage retest initiated...');
    return this.testLocalStorageAvailability();
  }

  static forceLocalStorage(): boolean {
    console.log('Forcing localStorage test...');
    this.isLocalStorageAvailable = true;
    return this.testLocalStorageAvailability();
  }

  static logStorageStatus(): void {
    console.log('=== COMPLETE STORAGE STATUS ===');
    const info = this.getStorageInfo();
    console.table(info);
    console.log('Fallback data keys:', Array.from(this.fallbackData.keys()));
    console.log('localStorage keys:', this.isLocalStorageAvailable ? Object.keys(localStorage) : 'Not available');
  }
}
