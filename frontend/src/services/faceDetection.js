import * as faceapi from 'face-api.js'

class FaceDetectionService {
  constructor() {
    this.modelsLoaded = false
    this.loadingPromise = null
  }

  async loadModels() {
    if (this.modelsLoaded) return true
    if (this.loadingPromise) return this.loadingPromise

    this.loadingPromise = this._loadModels()
    return this.loadingPromise
  }

  async _loadModels() {
    try {
      console.log('Loading face-api.js models...')

      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
      ])

      console.log('Face-api.js models loaded successfully')
      this.modelsLoaded = true
      return true
    } catch (error) {
      console.error('Error loading face-api.js models:', error)
      this.modelsLoaded = false
      throw error
    }
  }

  async detectFaceWithLandmarks(imageElement) {
    if (!this.modelsLoaded) {
      console.log('Models not loaded, loading now...')
      await this.loadModels()
    }

    try {
      console.log('Detecting face on image element...', {
        width: imageElement.naturalWidth || imageElement.width,
        height: imageElement.naturalHeight || imageElement.height,
        src: imageElement.src?.substring(0, 50) + '...'
      })

      const detection = await faceapi
        .detectSingleFace(imageElement, new faceapi.TinyFaceDetectorOptions({
          inputSize: 416,
          scoreThreshold: 0.3
        }))
        .withFaceLandmarks()
        .withFaceDescriptor()

      console.log('Face detection result:', detection)

      if (!detection) {
        console.warn('No face detected in image')

        // Debug: Let's try with different detector options
        console.log('🔄 Trying with relaxed detection settings...')
        const relaxedDetection = await faceapi
          .detectSingleFace(imageElement, new faceapi.TinyFaceDetectorOptions({
            inputSize: 320,
            scoreThreshold: 0.1
          }))

        console.log('Relaxed detection result:', relaxedDetection)

        if (!relaxedDetection) {
          throw new Error('Tidak ada wajah yang terdeteksi dalam gambar. Pastikan wajah terlihat jelas dan menghadap kamera.')
        }
      }

      return detection
    } catch (error) {
      console.error('Error detecting face:', error)
      throw new Error('Gagal mendeteksi wajah: ' + error.message)
    }
  }

  async detectFaceFromVideo(videoElement) {
    if (!this.modelsLoaded) {
      await this.loadModels()
    }

    try {
      const detection = await faceapi
        .detectSingleFace(videoElement, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor()

      return detection
    } catch (error) {
      console.error('Error detecting face from video:', error)
      return null
    }
  }

  validateFaceQuality(detection) {
    if (!detection) {
      return {
        isValid: false,
        message: 'Wajah tidak terdeteksi. Pastikan wajah terlihat jelas di kamera.'
      }
    }

    const { detection: faceBox, landmarks } = detection

    // Check confidence score
    if (faceBox.score < 0.5) {
      return {
        isValid: false,
        message: 'Kualitas deteksi wajah kurang baik. Mohon posisikan wajah dengan jelas.'
      }
    }

    // Check face size (minimum size)
    const faceWidth = faceBox.box.width
    const faceHeight = faceBox.box.height

    if (faceWidth < 100 || faceHeight < 100) {
      return {
        isValid: false,
        message: 'Wajah terlalu kecil. Mohon dekatkan wajah ke kamera.'
      }
    }

    // Check if face is roughly frontal using landmarks
    const leftEye = landmarks.getLeftEye()
    const rightEye = landmarks.getRightEye()

    // Calculate eye distance ratio
    const eyeDistance = Math.abs(leftEye[0].x - rightEye[3].x)
    const faceRatio = eyeDistance / faceWidth

    if (faceRatio < 0.25 || faceRatio > 0.8) {
      return {
        isValid: false,
        message: 'Mohon posisikan wajah menghadap lurus ke kamera.'
      }
    }

    return {
      isValid: true,
      message: 'Wajah terdeteksi dengan baik'
    }
  }

  compareFaces(descriptor1, descriptor2, threshold = 0.4) {
    if (!descriptor1 || !descriptor2) {
      return {
        isMatch: false,
        distance: 1,
        message: 'Data wajah tidak lengkap untuk perbandingan'
      }
    }

    try {
      const distance = faceapi.euclideanDistance(descriptor1, descriptor2)
      const isMatch = distance < threshold

      return {
        isMatch,
        distance: parseFloat(distance.toFixed(4)),
        confidence: parseFloat(((1 - distance) * 100).toFixed(2)),
        message: isMatch
          ? `Wajah cocok (confidence: ${parseFloat(((1 - distance) * 100).toFixed(2))}%)`
          : `Wajah tidak cocok (distance: ${parseFloat(distance.toFixed(4))})`
      }
    } catch (error) {
      console.error('Error comparing faces:', error)
      return {
        isMatch: false,
        distance: 1,
        message: 'Gagal membandingkan wajah'
      }
    }
  }

  async captureImageFromVideo(videoElement) {
    return new Promise((resolve) => {
      const canvas = document.createElement('canvas')
      const context = canvas.getContext('2d')

      // Set canvas size to match video
      canvas.width = videoElement.videoWidth
      canvas.height = videoElement.videoHeight

      console.log('📸 Capturing image:', {
        videoWidth: videoElement.videoWidth,
        videoHeight: videoElement.videoHeight,
        canvasWidth: canvas.width,
        canvasHeight: canvas.height
      })

      // Ensure good image quality
      context.imageSmoothingEnabled = true
      context.imageSmoothingQuality = 'high'

      // Draw the video frame to canvas
      context.drawImage(videoElement, 0, 0, canvas.width, canvas.height)

      // Convert to blob with high quality
      canvas.toBlob((blob) => {
        const file = new File([blob], 'face-capture.jpg', { type: 'image/jpeg' })
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9)

        console.log('✅ Image captured successfully', {
          fileSize: blob.size,
          dataUrlLength: dataUrl.length
        })

        resolve({
          file,
          dataUrl,
          canvas
        })
      }, 'image/jpeg', 0.9)
    })
  }

  drawFaceOverlay(canvas, detection, color = '#00ff00') {
    const ctx = canvas.getContext('2d')

    if (detection) {
      const { x, y, width, height } = detection.detection.box

      // Draw bounding box
      ctx.strokeStyle = color
      ctx.lineWidth = 2
      ctx.strokeRect(x, y, width, height)

      // Draw landmarks
      ctx.fillStyle = color
      const landmarks = detection.landmarks.positions

      landmarks.forEach(point => {
        ctx.beginPath()
        ctx.arc(point.x, point.y, 1, 0, 2 * Math.PI)
        ctx.fill()
      })
    }
  }
}

export default new FaceDetectionService()
