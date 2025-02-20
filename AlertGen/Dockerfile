FROM python:3.11

WORKDIR /app

RUN apt-get update && apt-get install -y ffmpeg x264 libx264-dev build-essential cmake git libavcodec-dev libavformat-dev libswscale-dev libgstreamer-plugins-base1.0-dev libgstreamer1.0-dev libgtk-3-dev libpng-dev libjpeg-dev libopenexr-dev libtiff-dev libwebp-dev libopencv-dev libssl-dev
COPY alert.mp3 /app/alert.mp3
COPY blankbg.png /app/blankbg.png
COPY fa-solid-900.ttf /app/fa-solid-900.ttf
COPY Poppins-Regular.ttf /app/Poppins-Regular.ttf
COPY requirements.txt /app/requirements.txt
RUN cd /app && pip install --no-binary opencv-python opencv-python
RUN cd /app && pip install -r requirements.txt

COPY videogen.py /app/videogen.py

CMD ["flask", "--app", "videogen", "run", "--host=0.0.0.0"]
