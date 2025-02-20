import os
import textwrap
import uuid
import time
from io import BytesIO

import cv2
from PIL import ImageFont, ImageDraw, Image, ImageColor
import numpy as np
from flask import Flask, request, send_file
from moviepy.audio.AudioClip import CompositeAudioClip
from moviepy.audio.io.AudioFileClip import AudioFileClip
from moviepy.video.io.VideoFileClip import VideoFileClip


app = Flask(__name__)
alert_img = cv2.imread("blankbg.png")
font = ImageFont.truetype("Poppins-Regular.ttf", 80)
header_font = ImageFont.truetype("Poppins-Regular.ttf", 120)
icon_font = ImageFont.truetype("fa-solid-900.ttf", 175)


@app.get("/")
def root():
    return {"message": "Hello, I am the Digital Signage Alert Generator."}


def get_alert_image(text, header, icon, header_color_hex, page=0, max=0):
    global alert_img, blank_img, font, header_font, icon_font
    cv2_im_rgb = cv2.cvtColor(alert_img, cv2.COLOR_BGR2RGB)
    pil_im = Image.fromarray(cv2_im_rgb)
    icon = int(icon, 16)
    draw = ImageDraw.Draw(pil_im)
    header_color = ImageColor.getcolor(header_color_hex, "RGB")
    draw.text((90, 65), chr(icon), font=icon_font, fill=header_color)
    draw.text((300, 75), header, font=header_font, fill=header_color)
    if page != 0 and max != 0:
        draw.text((1700, 115), f"{page}/{max}", font=font)
    draw.text((80, 300), text, font=font)
    return cv2.cvtColor(np.array(pil_im), cv2.COLOR_RGB2BGR)


@app.post('/generator')
def generate_alert():
    alert_text = request.form.get("alert_text").strip()
    header_text = request.form.get("header_text").strip()
    header_icon = request.form.get("header_icon").strip()
    header_color = request.form.get("header_color").strip()

    seconds_per_page = int(request.form.get("seconds_per_page", "10"))
    repeat_count = int(request.form.get("repeat_count", "4"))
    enable_alert_tone = (request.form.get("enable_alert_tone", "false").lower() == "true")
    enable_tts = (request.form.get("enable_tts", "false").lower() == "true")

    alert_id = str(uuid.uuid4())
    lines = textwrap.fill(alert_text, 42, drop_whitespace=False, replace_whitespace=False)
    string_batches = []
    string_batch = 0
    string_count = 0
    for line in lines.split("\n"):
        line = line.strip()
        if len(string_batches) <= string_batch:
            string_batches.append(line)
        else:
            string_batches[string_batch] = string_batches[string_batch] + "\n" + line
        string_count += 1
        if string_count >= 8:
            string_count = 0
            string_batch += 1

    img_array = []
    count = 0
    for page in string_batches:
        count += 1
        img_array.append(get_alert_image(page, header_text, header_icon, header_color, count, len(string_batches)))

    seconds_per_image = seconds_per_page
    fps = 1
    repeats = repeat_count

    if enable_tts or enable_alert_tone:
        out = cv2.VideoWriter(f"/tmp/output{alert_id}.mp4", cv2.VideoWriter_fourcc(*'avc1'), fps, (1920,1080))
    else:
        out = cv2.VideoWriter(f"/tmp/final{alert_id}.mp4", cv2.VideoWriter_fourcc(*'avc1'), fps, (1920, 1080))

    frames = seconds_per_image * fps
    if enable_alert_tone:
        #copy frames for alert tone
        eas_clip = AudioFileClip("alert.mp3")
        seconds = int(eas_clip.end) + 1
        for frame in range(frames):
            out.write(img_array[0])

    for total_repeat in range(repeats):
        for page_number in range(len(string_batches)):
            for frame in range(frames):
                out.write(img_array[page_number])

    out.release()

    if enable_tts:
        from gtts import gTTS
        tts = gTTS(alert_text.replace("\n", " ").replace("*", ""))
        tts.save(f"/tmp/tts{alert_id}.mp3")
        #print("TTS obtained.")

    if enable_tts or enable_alert_tone:
        video_clip = VideoFileClip(f"/tmp/output{alert_id}.mp4")
        if enable_tts:
            tts_clip = AudioFileClip(f"/tmp/tts{alert_id}.mp3")
        if enable_tts and enable_alert_tone:
            audio_clip = CompositeAudioClip([eas_clip, tts_clip.set_start(eas_clip.end + 1)])
        else:
            if enable_alert_tone:
                audio_clip = eas_clip
            elif enable_tts:
                audio_clip = tts_clip

        if video_clip.end < audio_clip.end:
            audio_clip = audio_clip.subclip(0, video_clip.end)
        final_audio = audio_clip
        final_clip = video_clip.set_audio(final_audio)
        final_clip.write_videofile(f"/tmp/final{alert_id}.mp4", audio_codec='aac', threads=4, logger=None)
        os.remove(f"/tmp/output{alert_id}.mp4")
        if enable_tts:
            os.remove(f"/tmp/tts{alert_id}.mp3")

    file_reply = send_file(f"/tmp/final{alert_id}.mp4", as_attachment=True,
                     mimetype="video/mp4", download_name=f"alert{alert_id}.mp4")

    os.remove(f"/tmp/final{alert_id}.mp4")

    return file_reply
